<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\Billing;
use App\Models\Publisher;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BillingService
{

    private PagarMeService $pagarMeService;
    private ChargesService $chargesService;
    private Billing $model;

    public function __construct(Billing $model, PagarMeService $pagarMeService, ChargesService $chargesService)
    {
        $this->model = $model;
        $this->pagarMeService = $pagarMeService;
        $this->chargesService = $chargesService;
    }

    /**
     * Resource search a database using filters
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function search($data, bool $jsonResponse = true)
    {
        try {
            //Apply filters
            if (isset($data["all"]) && $data["all"] == "true") {
                $resources = $this->model->applyShowWith($data)->get();
            } else {
                $resources = $this->model->applyShowWith($data)->paginate(Config::get('constant.pagination'));
            }

            return Helpers::reponse(true, $resources, 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource store a database
     *
     * @param Publisher $publisher
     * @param string $status
     * @param null $ref
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws CustomException
     */
    public function storeBillingPlan($data, $jsonResponse = true)
    {
        try {
            $this->model->fill($data);

            if ($this->model->save()) {
                return Helpers::reponse(true, $this->model, 200, [], $jsonResponse);
            } else {
                throw new Exception('Falha ao grava fatura');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse
     * @throws CustomException
     */
    public function extract($data, $jsonResponse = true)
    {
        try {
            $publisher = Publisher::find($data['publisher_id']);
            $lastBilling = $publisher->billings()->orderBy('id', 'desc')->first();

            $payload = [
                'payment_method' => $publisher->payment_method,
                'payment_situation' => $publisher->payment_situation,
                'can_reprocess' => $this->chargesService->canReprocessPayment($publisher),
                'access_status' => $publisher->access_status,
                'credit_card' => ($publisher->creditCardDefault ?? null),
                'last_billing' => ($lastBilling ?? null)
            ];

            return Helpers::reponse(true, $payload, 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * @param Publisher $publisher
     * @throws Exception
     */
    public function checkBillingsPlans(Publisher $publisher)
    {
        // Primeiro pagamento do Anunciante para PJ
        if ($publisher->payment_situation == 'first' && empty($publisher->external_subscription_id)) {
            $billing = new Billing([
                'reference' => Carbon::now()->format('m-Y'),
                'status' => 'pending_payment',
                'payment_method' => $publisher->payment_method,
                'description' => 'Cobrança referente a utilização do Plano: ' . $publisher->plan->name,
                'expiration' => null,
                'value' => $publisher->plan->value,
                'plan_id' => $publisher->plan_id,
                'publisher_id' => $publisher->id,
            ]);

            try {
                // Assina o plano
                $resultSubscription = $this->pagarMeService->storeSubscription($publisher);

                if (isset($resultSubscription->current_transaction)) {
                    $billing->status = $resultSubscription->current_transaction->status;
                    $billing->external_transaction_id = $resultSubscription->current_transaction->id;

                    if ($billing->payment_method == 'BOL') {
                        $billing->boleto_url = $resultSubscription->current_transaction->boleto_url;
                        $billing->boleto_barcode = $resultSubscription->current_transaction->boleto_barcode;
                    } else {
                        $billing->cred_card_info = $publisher->creditCardDefault->number;
                    }

                    if (!empty($resultSubscription->current_transaction->refuse_reason)) {
                        $billing->description = $billing->description . " -> " . $resultSubscription->current_transaction->refuse_reason;
                    }

                    if ($billing->status == 'paid') {
                        $billing->payed_at = Carbon::now();
                        $billing->expiration = Carbon::now()->addDays(($publisher->plan->recurrence * 30))->format('Y-m-d');
                    }
                }
                $billing->save();
                $publisher->save();
            } catch (Exception $e) {
                $billing->status = 'error';
                $billing->description = $billing->description . " -> " . $e->getMessage();
                $billing->save();
                $publisher->save();
                throw $e;
            }
        }
    }

}
