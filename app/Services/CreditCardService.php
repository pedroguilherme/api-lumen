<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\CreditCard;
use App\Traits\DefaultService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreditCardService implements DefaultServiceContracts
{
    use DefaultService;

    /**
     * @var PagarMeService
     */
    private PagarMeService $pagarMeService;

    public function __construct(CreditCard $model, PagarMeService $pagarMeService)
    {
        $this->model = $model;
        $this->pagarMeService = $pagarMeService;
    }


    /**
     * Resource store a database.
     *
     * @param $data
     * @param bool $jsonResponse
     * @return bool|Exception|JsonResponse
     * @throws Exception
     */
    public function store($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();

            if ($data['default'] == true) {
                $this->model::where('publisher_id', $data['publisher_id'])->update(['default' => false]);
            }

            // Check if resource created exist actually
            $resource = $this->model->fill([
                'default' => ($data['default'] ?? false),
                'number' => Str::substr($data['number'], 0, 4),
                'publisher_id' => $data['publisher_id'],
            ]);

            if ($resource->save()) {
                $resource = $this->pagarMeService->storeCreditCard($resource, $data);

                if (empty($resource->encrypted)) {
                    throw new Exception('Falha ao gravar cartão no Pagar.me');
                }

                $countCheck = $this->model::where('encrypted', $resource->encrypted)
                    ->where('publisher_id', $resource->publisher_id)
                    ->count();

                if ($countCheck == 1) {
                    DB::commit();
                    return Helpers::reponse(true, $this->show($resource, false), 201, [], $jsonResponse);
                } else {
                    return new CustomException('', 0, null, Config::get('errors.duplicate_credit_cart'));
                }
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

            if ($data['default'] == true) {
                $this->model::where('publisher_id', $data['publisher_id'])->update(['default' => false]);
            }

            // Check if resource created exist actually
            $resource = $this->model::find($data['id']);
            $resource->default = true;

            if ($resource->save()) {
                DB::commit();
                return Helpers::reponse(true, $resource, 200, [], $jsonResponse);
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
            DB::beginTransaction();

            // Get resource in database
            $resource = $this->model->applyShowWith()->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            if ($resource->default) {
                return Helpers::reponse(false, [], 406, Config::get('errors.payment_default'), $jsonResponse);
            }

            $result = $resource->forceDelete();

            if ($result) {
                DB::commit();
                return Helpers::reponse(true, [], 200, [], $jsonResponse);
            } else {
                throw new Exception('Falha ao atualizar no banco de dados');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }
}
