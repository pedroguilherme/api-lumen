<?php

namespace App\Http\Controllers\V1\Site\Structure;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\HomeService;
use App\Services\PublisherService;
use App\Services\SearchService;
use App\Services\VehicleService;
use App\Traits\DefaultController;
use App\Traits\ValidateCpfCnpj;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Request as RequestFacade;

class SearchController extends Controller
{
    private SearchService $service;

    private array $sortable = [
        'tipo' => 'type',
        'marca' => 'brand_id',
        'modelo' => 'model_id',
        'cidade' => 'city_id',
        'combustivel' => 'fuel_id',
        'cambio' => 'transmission_id',
        'carroceria' => 'bodytype_id',
        'anunciante' => 'publisher_id',
        'cor' => 'color_id',
        'ano' => 'year_model',
        'preco' => 'value',
        'quilometragem' => 'mileage',
        'delivery' => 'delivery',
        'unico_dono' => 'only_owner',
        'ipva_pago' => 'ipva_paid',
        'garantia' => 'warranty',
        'blindado' => 'armored',
        'sete_lugares' => 'seven_places',
        'revisao' => 'review',
        'order' => 'order',
        'all' => 'all'
    ];

    public function __construct(SearchService $service)
    {
        $this->service = $service;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function get(Request $request)
    {
        $filters = $request->only(array_keys($this->sortable));

        $data = [];
        foreach ($filters as $key => $filter) {
            $data[$this->sortable[$key]] = $filter;
        }

        $data = new Collection($data);

        return $this->service->getSite($data);
    }
}
