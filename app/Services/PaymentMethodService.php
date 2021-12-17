<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\CreditCard;
use App\Models\Publisher;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PaymentMethodService
{
    private CreditCardService $creditCardService;
    private ChargesService $chargesService;
    private Publisher $publisher;

    public function __construct(
        CreditCardService $creditCardService,
        ChargesService $chargesService,
        Publisher $publisher
    ) {
        $this->creditCardService = $creditCardService;
        $this->chargesService = $chargesService;
        $this->publisher = $publisher;
    }

    /**
     * Resource search a database using filters
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function search($data, $jsonResponse = true)
    {
        try {
            $publisher = $this->publisher::find($data['publisher_id']);

            if (empty($publisher)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            $resources = $this->creditCardService->search([
                'publisher_id' => $publisher->id,
                'active' => true,
                'all' => true
            ], false);

            $return = [
                'payment_method' => $publisher->payment_method,
                'credit_cards' => $resources
            ];

            return Helpers::reponse(true, $return, 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource store a database.
     * Check if resource created exist actually, if exist return error with resource
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function store($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();

            $publisher = $this->publisher::find($data["publisher_id"]);

            if (empty($publisher)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            // Em caso de cartão, checar se já possui default, se não coloca como principal
            if ($data["payment_method"] == 'CC') {
                $data['default'] = empty($publisher->payment_method);
                $creditCard = $this->creditCardService->store($data, false);
                if (($creditCard instanceof CreditCard) and empty($creditCard->encrypted)) {
                    throw new Exception('Falha ao gravar no banco de dados');
                } else {
                    if (!($creditCard instanceof CreditCard)) {
                        throw $creditCard;
                    }
                }
            }

            $firstTime = false;
            // Caso não tenha pagamento default, coloca o cadastrado como principal
            if (empty($publisher->payment_method)) {
                $publisher->payment_method = $data['payment_method'];
                $firstTime = true;
            }

            if ($publisher->save()) {
                if ($firstTime && $publisher->type == 'J') {
                    // Manda checar e cobrar
                    $this->chargesService->checkAndCharge($publisher);
                }
                DB::commit();
                return Helpers::reponse(true, $this->search($data, false), 201, [], $jsonResponse);
            } else {
                throw new Exception('Falha ao gravar no banco de dados');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource update a database.
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function update($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();

            // Get resource in database
            $publisher = $this->publisher::find($data["publisher_id"]);

            if (empty($publisher)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            $oldPaymentMethod = $publisher->payment_method;
            $publisher->payment_method = $data['payment_method'];
            if ($publisher->save()) {
                if ($publisher->payment_method == 'CC') {
                    $creditCard = $this->creditCardService->update([
                        'id' => $data["credit_card_id"],
                        'publisher_id' => $data["publisher_id"],
                        'default' => true,
                    ], false);

                    if (!($creditCard instanceof CreditCard)) {
                        throw new Exception('Falha ao atualizar no banco de dados');
                    }
                } else {
                    // Remover default de todos cartões
                    $creditCards = $publisher->creditCards;
                    foreach ($creditCards as $creditCard) {
                        $creditCard->default = false;
                        $creditCard->save();
                    }
                }

                if ($publisher->type == 'J') {
                    // Manda checar e cobrar
                    $this->chargesService->checkAndCharge($publisher, $oldPaymentMethod);
                }

                DB::commit();
                return Helpers::reponse(true, $this->search($data, false), 200, [], $jsonResponse);
            } else {
                throw new Exception('Falha ao atualizar no banco de dados');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource destroy or restore a database.
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function destroy($data, $jsonResponse = true)
    {
        try {
            // Get resource in database
            return $this->creditCardService->destroy($data);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }
}
