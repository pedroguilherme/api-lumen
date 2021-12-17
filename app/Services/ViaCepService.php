<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\City;
use App\Models\State;
use App\Traits\DefaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ViaCepService implements DefaultServiceContracts
{
    use DefaultService;

    private array $resources = [
        "city" => null,
        "state" => null,
        "cep" => null,
        "neighborhood" => null,
        "address" => null,
    ];

    public function __construct(City $model)
    {
        $this->model = $model;
    }

    /**
     * Resource search a database using filters
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|array|boolean
     * @throws CustomException
     */
    public function search($data, $jsonResponse = true)
    {
        try {
            $response = Http::get('https://viacep.com.br/ws/' . filter_var($data["cep"], FILTER_SANITIZE_NUMBER_INT) . '/json/unicode/');
            if ($response->ok()) {
                $body = $response->json();
                $resources["cep"] = $data["cep"];
                if (!isset($body["erro"])) {
                    $resources = [
                        "city" => $this->model::where("name", $body["localidade"])->first([
                            "id",
                            "name",
                            "state_id"
                        ]),
                        "state" => State::where("uf", $body["uf"])->first(["uf", "name"]),
                        "neighborhood" => $body["bairro"],
                        "address" => $body["logradouro"],
                    ];
                }
                return Helpers::reponse(true, $resources, 200, [], $jsonResponse);
            } else {
                return Helpers::reponse(false, [], 400, Config::get('errors.invalid'), $jsonResponse);
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }
}
