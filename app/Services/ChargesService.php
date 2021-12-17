<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Mail\AdExpired;
use App\Mail\BoletoPayment;
use App\Mail\ExpirationReminder;
use App\Mail\FailedPayment;
use App\Mail\FreeTrialExpired;
use App\Mail\FreeTrialExpiredAdmin;
use App\Mail\SuccessPayment;
use App\Models\Billing;
use App\Models\Publisher;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Sentry\State\Scope;

use function Sentry\configureScope;

class ChargesService
{
    private int $daysMonth = 30;
    private ?string $type;

    /**
     * ChargesService constructor.
     * @param string|null $type
     */
    public function __construct(string $type = null)
    {
        $this->type = $type;
    }

    /**
     * @param Publisher $publisher
     * @return false|int
     * @throws CustomException
     */
    public function updateFreeActive(Publisher $publisher)
    {
        if (!empty($publisher->free_active_date) && !empty($publisher->free) &&
            $publisher->payment_situation != 'canceled' && $publisher->payment_situation != 'paid') {
            $freeActiveDate = Carbon::parse($publisher->free_active_date);
            $now = Carbon::now();
            $diff = $freeActiveDate->diffInDays($now, false);
            if ($diff >= 0 && $diff <= ($publisher->free)) {
                $publisher->payment_situation = 'free';
                $publisher->access_status = 'full';
                $publisher->save();
            } else {
                if ($publisher->payment_situation == 'free') {
                    if ($diff >= 0 && $diff <= ($publisher->free + 15)) {
                        try {
                            $this->checkAndCharge($publisher, null, true);
                            $publisherCheck = Publisher::query()->find($publisher->id);
                            if ($publisherCheck->payment_situation != 'paid') {
                                $publisherCheck->payment_situation = 'free';
                                $publisherCheck->access_status = 'full';
                                $publisherCheck->save();
                            }
                        } catch (Exception $exception) {
                            $publisher->payment_situation = 'free';
                            $publisher->access_status = 'full';
                            $publisher->save();
                        }
                    } else {
                        Log::info('ChargeService carência expirada: ' . $publisher->id);
                        $publisher->payment_situation = 'first';
                        $publisher->access_status = 'limited';
                        $publisher->save();
                        // Faz a cobrança da loja, caso tenha pagamento ativo.
                        $this->checkAndCharge($publisher, null, true);
                    }
                }
            }
            return $diff;
        } else {
            if ($publisher->payment_situation == 'free') {
                $publisher->payment_situation = 'first';
                $publisher->access_status = 'limited';
                $publisher->save();
            }
        }
        return false;
    }

    /**
     * @param Publisher $publisher
     * @return bool
     * @throws CustomException
     */
    public function canReprocessPayment(Publisher $publisher)
    {
        $situations = ['refused', 'unpaid', 'waiting_payment', 'first_waiting_payment'];
        if (in_array($publisher->payment_situation, $situations)) {
            $billing = $publisher->lastBilling;
            if (!empty($billing)) {
                $pagarMe = new PagarMeService();
                $now = Carbon::now();
                $plan = $publisher->plan;
                $recurrence = $plan->recurrence;

                $transaction = $pagarMe->checkTransaction($billing);

                $expirationBoleto = Carbon::parse($transaction->boleto_expiration_date);
                $diffDaysExpiration = $now->floatDiffInRealDays($expirationBoleto, false);

                $billing->status = $transaction->status;
                if ($publisher->payment_method == 'BOL' && $diffDaysExpiration < 0 && $transaction->status != 'paid') {
                    $billing->status = 'unpaid';
                }

                $billing->payed_at = ($billing->status == 'paid' ? $now : null);
                $billing->expiration = ($billing->status == 'paid' ? Carbon::parse($now)->addDays(($this->daysMonth * $recurrence)) : null);
                $billing->save();

                $publisher->payment_situation = $billing->status;
                $publisher->payment_nextcheck = ($publisher->payment_situation == 'paid' ? $billing->expiration : Carbon::parse($now)->addDay());
                $publisher->paid_in = ($publisher->payment_situation == 'paid' ? $now : null);
                $publisher->access_status = ($publisher->payment_situation == 'paid' ? 'full' : $publisher->access_status);
                $publisher->save();

                if ($publisher->payment_situation == 'paid') {
                    Mail::to($publisher->user)->queue(new SuccessPayment($publisher));
                    return false;
                }
                if ($diffDaysExpiration > 0) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param Vehicle $vehicle
     * @param Publisher $publisher
     * @return JsonResponse|bool
     * @throws CustomException
     */
    public function canReprocessPaymentPF(Vehicle $vehicle, Publisher $publisher)
    {
        $situations = ['first', 'refused', 'unpaid', 'expired', 'waiting_payment'];
        if (in_array($vehicle->payment_status, $situations)) {
            if ($vehicle->payment_status == 'waiting_payment') {
                $pagarMe = new PagarMeService();
                $lastBilling = $vehicle->lastBilling;
                $plan = Config::get('constant.pf_plans.' . $lastBilling->plan_pf);
                $expiration = $plan['expiration'];
                $transaction = $pagarMe->checkTransaction($lastBilling);
                if (isset($transaction->status)) {
                    $lastBilling->status = $transaction->status;
                    $lastBilling->payed_at = ($lastBilling->status == 'paid' ? Carbon::now() : null);
                    $lastBilling->expiration = ($lastBilling->status == 'paid' ? Carbon::now()->addDays($expiration) : null);
                    $lastBilling->save();

                    if ($lastBilling->status == 'paid') {
                        $vehicle->payment_status = $lastBilling->status;
                        $vehicle->disable_on = empty($plan['expiration']) ? null : Carbon::now()->addDays($plan['expiration']);
                        $vehicle->save();
                        Mail::to($publisher->user)->queue(new SuccessPayment($publisher));
                        $return = false;
                    } else {
                        if ($publisher->payment_method == 'BOL') {
                            $expirationBoleto = Carbon::parse($transaction->boleto_expiration_date);
                            $diffDaysExpiration = Carbon::now()->floatDiffInRealDays($expirationBoleto, false);
                            if ($diffDaysExpiration < 0) {
                                // Edita o billing antigo para unpaid.
                                $lastBilling->status = 'unpaid';
                                $lastBilling->save();
                                $vehicle->payment_status = 'unpaid';
                                $vehicle->save();
                                $return = true;
                            } else {
                                $return = false;
                            }
                        } else {
                            $lastBilling->status = 'unpaid';
                            $lastBilling->save();
                            $vehicle->payment_status = 'unpaid';
                            $vehicle->save();
                            $return = true;
                        }
                    }
                }
            } else {
                $return = true;
            }
        } else {
            $return = false;
        }
        return ($return ?? false);
    }

    /**
     * @param Publisher $publisher
     * @param bool $jsonResponse
     * @return JsonResponse
     * @throws CustomException
     */
    public function reprocessPayment(Publisher $publisher, bool $jsonResponse = true)
    {
        try {
            if ($this->canReprocessPayment($publisher)) {
                $return = $this->makeInvoice($publisher);
                if ($return !== false) {
                    return Helpers::reponse(true, $return, 200, [], $jsonResponse);
                } else {
                    return Helpers::reponse(false, [], 404, Config::get('errors.payment_method_not_found'));
                }
            }
            return Helpers::reponse(false, [], 406, Config::get('errors.not_allowed'));
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * @param Vehicle $vehicle
     * @param Publisher $publisher
     * @param bool $jsonResponse
     * @return JsonResponse
     * @throws CustomException
     */
    public function reprocessPaymentPF(Vehicle $vehicle, Publisher $publisher, $jsonResponse = true)
    {
        try {
            if ($this->canReprocessPaymentPF($vehicle, $publisher)) {
                $plans = new Collection(Config::get('constant.pf_plans'));
                $plan = $plans->where('spotlight', $vehicle->spotlight)->first();

                if (empty($plan)) {
                    return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
                }

                $status = $this->makeInvoice($publisher, null, $plan, $vehicle);
                if ($status !== false) {
                    $vehicle->payment_status = $status;
                    $vehicle->disable_on = empty($plan['expiration']) && $status != 'paid' ? null : Carbon::now()->addDays($plan['expiration']);
                    $vehicle->save();
                    return Helpers::reponse(true, $status, 200, [], $jsonResponse);
                } else {
                    return Helpers::reponse(false, [], 404, Config::get('errors.payment_method_not_found'));
                }
            }
            return Helpers::reponse(false, [], 406, Config::get('errors.not_allowed'));
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * @param Publisher $publisher
     * @return bool
     * @throws CustomException
     */
    public function checkPaidStatus(Publisher $publisher)
    {
        // Loja efetuou o pagamento
        if ($publisher->payment_situation == 'paid') {
            $now = Carbon::now();
            $expirationPlan = Carbon::parse($publisher->payment_nextcheck);
            $diffDaysExpiration = $now->diffInDays($expirationPlan, false);
            // Plano Expirou
            if ($publisher->payment_method == 'BOL') {
                if ($diffDaysExpiration <= 5 && $diffDaysExpiration >= 0) {
                    // Renovar o plano
                    $publisher = $this->updateFuturePlan($publisher);
                    $this->makeInvoice($publisher, $publisher->payment_nextcheck);
                    return true;
                } else {
                    // Plano ativo
                    return true;
                }
            } else {
                if ($diffDaysExpiration == 0) {
                    // Renovar o plano
                    $publisher = $this->updateFuturePlan($publisher);
                    $this->makeInvoice($publisher);
                    return true;
                } else {
                    // Plano ativo
                    return true;
                }
            }
        }
        return false;
    }

    private function updateFuturePlan(Publisher $publisher)
    {
        if (!empty($publisher->future_plan_id)) {
            $publisher->plan_id = $publisher->future_plan_id;
            $publisher->future_plan_id = null;
            $publisher->save();
            $publisher->unsetRelation('plan');
        }
        return $publisher;
    }

    /**
     * @param Publisher $publisher
     * @param null $oldPaymentMethod
     * @param bool $freeTrial
     * @return Publisher|false
     * @throws CustomException
     */
    public function checkAndCharge(Publisher $publisher, $oldPaymentMethod = null, bool $freeTrial = false)
    {
        $situations = ['first', 'waiting_payment', 'first_waiting_payment', 'refused'];
        if (in_array($publisher->payment_situation, $situations) || $freeTrial) {
            // Se era boleto e continua boleto, não faz nada.
            if ($oldPaymentMethod == 'BOL' && $publisher->payment_method == 'BOL') {
                return $publisher;
            }

            // Se era boleto e estava aguardando pagamento faz a checagem da tansação, se não estiver pago, processa o pagamento.
            $billing = $publisher->lastBilling;
            if (
                ($oldPaymentMethod == 'BOL' && $publisher->payment_situation == 'waiting_payment') ||
                ($freeTrial && $publisher->payment_method == 'BOL' && !empty($billing) && $billing->status == 'waiting_payment')
            ) {
                $now = Carbon::now();
                $plan = $publisher->plan;
                $recurrence = $plan->recurrence;
                $pagarMe = new PagarMeService();
                $transaction = $pagarMe->checkTransaction($billing);

                $billing->status = $transaction->status;
                $billing->payed_at = ($billing->status == 'paid' ? $now : null);
                $billing->expiration = ($billing->status == 'paid' ? Carbon::parse($now)->addDays(($this->daysMonth * $recurrence)) : null);
                $billing->save();

                $publisher->payment_situation = $billing->status;
                $publisher->payment_nextcheck = ($publisher->payment_situation == 'paid' ? $billing->expiration : Carbon::parse($now)->addDay());
                $publisher->paid_in = ($publisher->payment_situation == 'paid' ? $now : null);
                $publisher->access_status = ($publisher->payment_situation == 'paid' ? 'full' : $publisher->access_status);
                $publisher->save();
                if ($publisher->payment_situation == 'paid') {
                    Mail::to($publisher->user)->queue(new SuccessPayment($publisher));
                    return $publisher;
                } else {
                    $billing->status = 'unpaid';
                    $billing->save();
                }
            }

            return $this->makeInvoice($publisher);
        } else {
            return false;
        }
    }

    /**
     * @param Publisher $publisher
     * @param null $boletoExpiration
     * @param array|null $planPF
     * @param Vehicle|null $vehicle
     * @return bool
     * @throws CustomException
     * @throws Exception
     */
    public function makeInvoice(
        Publisher $publisher,
        $boletoExpiration = null,
        array $planPF = [],
        Vehicle $vehicle = null
    ) {
        if (!empty($publisher->payment_method)) {
            $pagarMe = new PagarMeService();
            $now = Carbon::now();

            if ($publisher->type == 'J') {
                $plan = $publisher->plan;
                $value = $plan->value;
                $expiration = $this->daysMonth * $plan->recurrence;
            } else {
                if (!empty($planPF)) {
                    $plan = $planPF;
                    $value = $plan['value'];
                    $expiration = $planPF['expiration'];
                } else {
                    throw new Exception('Falha ao processar pagamento');
                }
            }

            $transaction = $pagarMe->makeTransaction($publisher, $value, 'plan', $boletoExpiration, $planPF);

            // Salva o Log de transação
            $billing = new Billing([
                'reference' => $now->format("m-Y"),
                'status' => ($transaction->status ?? 'failed'),
                'description' => 'Pagamento: ' . ($publisher->type == 'J' ? $publisher->plan->name : $planPF['name']),
                'payment_method' => $publisher->payment_method,
                'expiration' => (($transaction->status ?? null) == 'paid' ? Carbon::parse($now)->addDays($expiration) : null),
                'payed_at' => (($transaction->status ?? null) == 'paid' ? $now : null),
                'value' => $value,
                'cred_card_info' => ($publisher->creditCardDefault->number ?? null),
                'plan_id' => ($publisher->type == 'J' ? $plan->id : null),
                'plan_pf' => ($publisher->type == 'F' ? $planPF['tag'] : null),
                'publisher_id' => $publisher->id,
                'boleto_url' => ($transaction->boleto_url ?? null),
                'boleto_barcode' => ($transaction->boleto_barcode ?? null),
                'external_transaction_id' => ($transaction->id ?? 0),
            ]);

            if (!empty($vehicle)) {
                $billing->vehicle_id = $vehicle->id;
            }

            $billing->save();

            $status = ($transaction->status ?? 'failed');

            if ($publisher->type == 'J') {
                if ($status != 'paid' && $publisher->payment_situation == 'first') {
                    // Caso for boleto, mantem como first_waiting_payment para validações futuras.
                    $publisher->payment_situation = 'first' . ($status == 'waiting_payment' ? '_' . $status : '');
                } else {
                    $publisher->payment_situation = $status;
                }

                $publisher->access_status = $publisher->payment_situation == 'paid' ? 'full' : $publisher->access_status;

                if ($status == 'paid') {
                    $publisher->payment_nextcheck = $billing->expiration;
                } else {
                    if (empty($publisher->payment_nextcheck)) {
                        $publisher->payment_nextcheck = Carbon::parse($now)->addDay();
                    }
                }

                $publisher->paid_in = ($publisher->payment_situation == 'paid' ? $now : null);
                $publisher->save();
            }

            if ($status == 'paid') {
                Mail::to($publisher->user)->queue(new SuccessPayment($publisher));
            } else {
                // Boleto
                if ($status == 'waiting_payment') {
                    Mail::to($publisher->user)->queue(new BoletoPayment($publisher, $billing));
                } else {
                    Mail::to($publisher->user)->queue(new FailedPayment($publisher));
                }
            }

            return $status;
        } else {
            return false;
        }
    }

    /**
     */
    public function __invoke(Publisher $publisher = null)
    {
        // 5400 segundos (1 hora e meia) para exec desse script...
        set_time_limit(5400);

        // Loga, informando que o arquivo rodou.
        Log::info('Crontab ChargesService Rodou, type: ' . ($this->type ?? 'N/I'));

        // Faz o checagem e faturamento diário
        $now = Carbon::now();

        if (isset($publisher->id) && !empty($publisher->id)) {
            $publishers = new Collection([$publisher]);
        } else {
            $publishers = Publisher::query();
            if (!empty($this->type)) {
                $publishers = $publishers
                    ->where('type', $this->type)
                    ->where('payment_situation', '!=', 'canceled');
            }
            $publishers = $publishers->get();
        }

        $pagarMe = new PagarMeService();

        foreach ($publishers as $publisher) {
            try {
                Log::info('ChargeService rodando para: ' . $publisher->id);
                if ($publisher->type == 'J') {
                    if (!empty($publisher->free) && !empty($publisher->free_active_date)) {
                        $diff = $this->updateFreeActive($publisher);
                        // Possui carência ativa
                        if ($diff > 0 && $diff <= ($publisher->free + 15)) {
                            if (($publisher->free - $diff) == 5) {
                                Log::info('ChargeService disparos de e-mail de carência: ' . $publisher->id);
                                Mail::to($publisher->user)->queue(new FreeTrialExpired($publisher));
                                $mailList = User::where('type', '=', 'A')->whereNull('deleted_at')->get();
                                Mail::to($mailList)->queue(new FreeTrialExpiredAdmin($publisher));
                                Mail::to(['Usuário Teste' => 'alanranghetti@yahoo.com.br'])
                                    ->queue(new FreeTrialExpiredAdmin($publisher));
                            }
                            continue;
                        }
                    }
                    // Não possui carência ou carência já foi
                    $expirationReminder = false;
                    // Loja efetuou o pagamento
                    if ($this->checkPaidStatus($publisher)) {
                        Log::info('ChargeService checkPaidStatus: true : ' . $publisher->id);
                        continue;
                    }

                    // Para ambos, quando bater 15 dias, não envia mais e-mails e cancela o plano do lojista.

                    // Loja foi cobrada do pagamento sistema está aguardando o pagamento do boleto

                    $situations = ['waiting_payment', 'processing', 'first_waiting_payment'];
                    if (in_array($publisher->payment_situation, $situations)) {
                        Log::info('ChargeService 495: ' . $publisher->id);
                        $plan = $publisher->plan;
                        $value = $plan->value;
                        $recurrence = $plan->recurrence;
                        $billing = $publisher->lastBilling;
                        $transaction = $pagarMe->checkTransaction($billing);
                        // Checa se a ultima transação foi paga
                        if (isset($transaction->status)) {
                            $billing->status = $transaction->status;
                            $billing->payed_at = ($billing->status == 'paid' ? $now : null);
                            $billing->expiration = ($billing->status == 'paid' ? Carbon::parse($now)->addDays(($this->daysMonth * $recurrence)) : null);
                            $billing->save();

                            $publisher->payment_situation = $billing->status;
                            if ($publisher->payment_situation == 'paid') {
                                $publisher->payment_nextcheck = $billing->expiration;
                            } else {
                                if (empty($publisher->payment_nextcheck)) {
                                    $publisher->payment_nextcheck = Carbon::parse($now)->addDay();
                                }
                            }
                            $publisher->paid_in = ($publisher->payment_situation == 'paid' ? $now : null);
                            $publisher->save();

                            // Caso do boleto pago, mandar e-mail
                            if ($publisher->payment_situation == 'paid') {
                                Log::info('ChargeService pagou boleto: ' . $publisher->id);
                                Mail::to($publisher->user)->queue(new SuccessPayment($publisher));
                                continue;
                            }

                            // Se for boleto e não estiver pago -> Verificar vencimento do boleto
                            // Caso estiver vencido gera um novo boleto.
                            if ($publisher->payment_method == 'BOL') {
                                // Gerar um novo boleto caso necessário
                                $expirationBoleto = Carbon::parse($transaction->boleto_expiration_date);
                                $diffDaysExpiration = $now->floatDiffInRealDays($expirationBoleto, false);
                                if ($diffDaysExpiration <= 0) {
                                    // Edita o billing antigo para unpaid.
                                    $billing->status = 'unpaid';
                                    $billing->save();
                                    // Primeira vez e boleto venceu, não faz mais nada... aguarda a loja regerar um novo boleto.
                                    if ($publisher->payment_situation == 'first_waiting_payment') {
                                        $publisher->payment_situation = 'unpaid';
                                        $publisher->payment_nextcheck = null;
                                        $publisher->paid_in = null;
                                        $publisher->save();
                                    } else {
                                        $transaction = $pagarMe->makeTransaction($publisher, $value, 'plan');

                                        // Salva o Log de transação
                                        $billing = new Billing([
                                            'reference' => $now->format("m-Y"),
                                            'status' => ($transaction->status ?? 'failed'),
                                            'description' => 'Pagamento: ' . $publisher->plan->name,
                                            'payment_method' => $publisher->payment_method,
                                            'expiration' => (($transaction->status ?? null) == 'paid' ? Carbon::parse($now)->addDays(($this->daysMonth * $recurrence)) : null),
                                            'payed_at' => (($transaction->status ?? null) == 'paid' ? $now : null),
                                            'value' => $value,
                                            'cred_card_info' => ($publisher->creditCardDefault->number ?? null),
                                            'plan_id' => $plan->id,
                                            'publisher_id' => $publisher->id,
                                            'boleto_url' => ($transaction->boleto_url ?? null),
                                            'boleto_barcode' => ($transaction->boleto_barcode ?? null),
                                            'external_transaction_id' => ($transaction->id ?? 0),
                                        ]);
                                        $billing->save();

                                        $publisher->payment_situation = ($transaction->status ?? 'failed');
                                        $publisher->payment_nextcheck = ($publisher->payment_situation == 'paid' ? $billing->expiration : Carbon::parse($now)->addDay());
                                        $publisher->paid_in = ($publisher->payment_situation == 'paid' ? $now : null);
                                        $publisher->save();

                                        if ($publisher->payment_situation == 'paid') {
                                            Mail::to($publisher->user)->queue(new SuccessPayment($publisher));
                                            continue;
                                        } else {
                                            $expirationReminder = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $situations = ['waiting_payment', 'processing', 'refused', 'paid', 'unpaid'];
                    if (in_array($publisher->payment_situation, $situations)) {
                        // Regra dos 5, 10, 15
                        $expirationPlan = Carbon::parse($publisher->payment_nextcheck);
                        $diffDays = $now->diffInDays($expirationPlan, false);
                        $billing = $publisher->lastBilling;
                        if (in_array($diffDays, [-5, -10, -15])) {
                            $expirationReminder = true;
                        } else {
                            if ($diffDays < -15 && $publisher->access_status != 'limited') {
                                $publisher->payment_situation = 'unpaid';
                                $publisher->access_status = 'limited';
                                $publisher->save();
                                $billing->status = 'unpaid';
                                $billing->save();

                                // Inativar anúncios
                                $vehicles = $publisher->vehicles;
                                foreach ($vehicles as $vehicle) {
                                    $vehicle->delete();
                                }
                            }
                        }
                    }

                    if ($expirationReminder && isset($billing)) {
                        Log::info('ChargeService ExpirationReminder: ' . $publisher->id);
                        Mail::to($publisher->user)->queue(new ExpirationReminder($publisher, $billing));
                    }
                } else {
                    $vehicles = $publisher->vehicles;
                    foreach ($vehicles as $vehicle) {
                        if ($vehicle->payment_status == 'paid') {
                            if (!empty($vehicle->disable_on)) {
                                $diff = Carbon::now()->floatDiffInRealDays($vehicle->disable_on, false);
                                if ($diff < 0) {
                                    $vehicle->payment_status = 'expired';
                                    $vehicle->save();
                                    $lastBilling = $vehicle->lastBilling;
                                    Log::info('ChargeService AdExpired: ' . $publisher->id);
                                    Mail::to($publisher->user)->queue(new AdExpired($publisher, $lastBilling));
                                }
                            }
                        }

                        $situations = ['waiting_payment', 'processing'];
                        if (in_array($vehicle->payment_status, $situations)) {
                            $lastBilling = $vehicle->lastBilling;
                            $plan = Config::get('constant.pf_plans.' . $lastBilling->plan_pf);
                            $expiration = $plan['expiration'];
                            $transaction = $pagarMe->checkTransaction($lastBilling);
                            if (isset($transaction->status)) {
                                $lastBilling->status = $transaction->status;
                                $lastBilling->payed_at = ($lastBilling->status == 'paid' ? $now : null);
                                $lastBilling->expiration = ($lastBilling->status == 'paid' ? Carbon::parse($now)->addDays($expiration) : null);
                                $lastBilling->save();

                                if ($lastBilling->status == 'paid') {
                                    $vehicle->payment_status = $lastBilling->status;
                                    $vehicle->disable_on = empty($plan['expiration']) ? null : Carbon::now()->addDays($plan['expiration']);
                                    $vehicle->save();
                                    Log::info('ChargeService SuccessPayment: ' . $publisher->id);
                                    Mail::to($publisher->user)->queue(new SuccessPayment($publisher));
                                    continue;
                                } else {
                                    $expirationBoleto = Carbon::parse($transaction->boleto_expiration_date);
                                    $diffDaysExpiration = $now->floatDiffInRealDays($expirationBoleto, false);
                                    if ($diffDaysExpiration < 0) {
                                        // Edita o billing antigo para unpaid.
                                        $lastBilling->status = 'unpaid';
                                        $lastBilling->save();
                                        $vehicle->payment_status = 'unpaid';
                                        $vehicle->save();
                                        Log::info('ChargeService ExpirationReminder: ' . $publisher->id);
                                        Mail::to($publisher->user)
                                            ->queue(new ExpirationReminder($publisher, $lastBilling));
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (Exception $exception) {
                // Send the exception to sentry
                configureScope(function (Scope $scope): void {
                    $user = Auth::user();
                    if (!empty($user)) {
                        $scope->setUser($user->toArray());
                    }
                });
                app('sentry')->captureException($exception);
                // Log error in file
                Helpers::logCodeError($exception);
            }
        }
    }
}
