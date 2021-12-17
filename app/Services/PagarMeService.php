<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\Billing;
use App\Models\CreditCard;
use App\Models\Plan;
use App\Models\Publisher;
use ArrayObject;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use PagarMe\Client;

class PagarMeService
{

    private Client $pagarMe;

    private string $env;

    public function __construct()
    {
        $this->env = Config::get('app.env');

        $apiKey = $this->env == 'production' ? Config::get('pagarme.production.api') : Config::get('pagarme.testing.api');
        $this->pagarMe = new Client($apiKey);
    }

    /**
     * @param Publisher $publisher
     * @param $value
     * @param string $type
     * @param string|null $boletoExpiration
     * @param array $planPF
     * @return ArrayObject|JsonResponse
     * @throws CustomException
     */
    public function makeTransaction(Publisher $publisher, $value, $type = 'plan', $boletoExpiration = null, array $planPF = [])
    {
        try {
            $payload = [
                'amount' => intval(round($value * 100)),
                'customer' => [
                    'email' => ($publisher->emails[0]->value ?? $publisher->user->email),
                    'name' => $publisher->name,
                    'external_id' => (string)$publisher->id,
                    'type' => ($publisher->type == 'J' ? 'corporation' : 'individual'),
                    'country' => 'br',
                    'documents' => [
                        [
                            'type' => ($publisher->type == 'J' ? 'cnpj' : 'cpf'),
                            'number' => preg_replace('/[^0-9]/', '', $publisher->cpf_cnpj),
                        ]
                    ],
                    'phone_numbers' => [
                        '+55' . preg_replace('/[^0-9]/', '', ($publisher->phones[0]->value ?? '999999999')),
                    ],
                ],
                'billing' => [
                    'name' => $publisher->name,
                    'address' => [
                        'country' => 'br',
                        'street' => $publisher->address,
                        'street_number' => $publisher->number,
                        'state' => $publisher->city->state_id,
                        'city' => $publisher->city->name,
                        'neighborhood' => $publisher->neighborhood,
                        'zipcode' => preg_replace('/[^0-9]/', '', $publisher->cep)
                    ]
                ],
                'items' => [
                    [
                        'id' => (string) ($publisher->plan->id ?? $planPF['tag']),
                        'title' => ($publisher->plan->name ?? $planPF['name']),
                        'unit_price' => intval(round($value * 100)),
                        'quantity' => 1,
                        'tangible' => false
                    ],
                ],
                'metadata' => [
                    'publisher_id' => $publisher->id,
                    'plan_id' => ($publisher->plan->id ?? $planPF['tag'])
                ]
            ];

            $paymentMethod = 'boleto';
            if ($publisher->payment_method == 'CC') {
                $payload['card_id'] = $publisher->creditCardDefault->encrypted;
                $paymentMethod = 'credit_card';
            } else {
                if (!empty($boletoExpiration)) {
                    $payload['boleto_expiration_date'] = $boletoExpiration;
                }
            }

            $payload['payment_method'] = $paymentMethod;

            $transaction = $this->pagarMe->transactions()->create($payload);

            if (!empty($transaction) && isset($transaction->id)) {
                return $transaction;
            } else {
                throw new Exception('Falha ao tentar criar Transação no PagarMe');
            }
        } catch (Exception $e) {
            $errors = ['type' => 'PagarMe', 'data' => $e->getMessage()];
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    /**
     * @param Billing $billing
     * @return ArrayObject|JsonResponse
     * @throws CustomException
     */
    public function checkTransaction(Billing $billing)
    {
        try {
            $payload = [
                'id' => $billing->external_transaction_id,
            ];

            $transaction = $this->pagarMe->transactions()->get($payload);

            if (!empty($transaction) && isset($transaction->id)) {
                return $transaction;
            } else {
                throw new Exception('Falha ao tentar criar Transação no PagarMe');
            }
        } catch (Exception $e) {
            $errors = ['type' => 'PagarMe', 'data' => $e->getMessage()];
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    /**
     * @param CreditCard $creditCard
     * @param array $data
     * @return CreditCard|JsonResponse
     * @throws CustomException
     */
    public function storeCreditCard(CreditCard $creditCard, array $data)
    {
        try {
            $payLoad = [
                'holder_name' => $data['holder_name'],
                'number' => preg_replace('/[^0-9]/', '', $data['number']),
                'expiration_date' => Carbon::createFromFormat('m/Y', $data['expiration_date'])->format('my'),
                'cvv' => $data['cvv']
            ];
            $card = $this->pagarMe->cards()->create($payLoad);
            if (!empty($card) && isset($card->id)) {
                $creditCard->encrypted = $card->id;
                $creditCard->save();
            } else {
                throw new CustomException('Falha ao tentar criar Cartão no PagarMe');
            }
            return $creditCard;
        } catch (Exception $e) {
            $errors = ['type' => 'PagarMe', 'data' => $e->getMessage()];
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    /**
     * @param $model
     * @param $model_id
     * @return array
     */
    public function getPostBack($model, $model_id)
    {
        return Config::all();
    }

    /**
     * @param Plan $plan
     * @return Plan|JsonResponse
     * @throws CustomException
     */
    public function storePlan(Plan $plan)
    {
        try {
            if (empty($plan->external_plan_id)) {
                $planPagarMe = $this->pagarMe->plans()->create([
                    'amount' => ($plan->value * 100),
                    'days' => ($plan->recurrence * 30),
                    'name' => ($plan->name),
                    'invoice_reminder' => 5
                ]);

                if (!empty($planPagarMe) && isset($planPagarMe->id)) {
                    $plan->external_plan_id = $planPagarMe->id;
                    $plan->save();
                } else {
                    throw new Exception('Falha ao tentar criar plano no PagarMe');
                }
            }

            return $plan;
        } catch (Exception $e) {
            $errors = ['type' => 'PagarMe', 'data' => $e->getMessage()];
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    /**
     * @return ArrayObject|JsonResponse
     * @throws CustomException
     */
    public function getPlans()
    {
        try {
            return $this->pagarMe->plans()->getList();
        } catch (Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    public function payBoleto($transactionId)
    {
        try {
            return $this->pagarMe->transactions()->simulateStatus([
                'id' => $transactionId,
                'status' => 'paid'
            ]);
        } catch (Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }
}
