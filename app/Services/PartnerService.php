<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Helpers\HelpersSite;
use App\Models\Contact;
use App\Models\Publisher;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PartnerService
{
    /**
     * @param array $filters
     * @return JsonResponse
     * @throws CustomException
     */
    public function getPartners(array $filters = []): JsonResponse
    {
        try {
            $partners = DB::table('publishers as p')
                ->select([
                    'p.id',
                    'p.name',
                    'p.company_name',
                    'p.cpf_cnpj',
                    'p.cep',
                    'p.logo',
                    'p.number',
                    'p.neighborhood',
                    'p.address',
                    'p.complement',
                    'p.description',
                    'p.work_schedule',
                    DB::raw('count(v.id) as vehicles'),
                ])
                ->join('vehicles as v', function ($join) {
                    $join->on('v.publisher_id', '=', 'p.id')
                        ->whereNull('v.deleted_at');
                })
                ->join('cities as c', 'p.city_id', '=', 'c.id')
                ->whereIn('p.payment_situation', ['paid', 'free'])
                ->where('p.type', 'J')
                ->groupBy([
                    'p.id',
                    'p.name',
                    'p.company_name',
                    'p.cpf_cnpj',
                    'p.cep',
                    'p.logo',
                    'p.number',
                    'p.neighborhood',
                    'p.address',
                    'p.complement',
                    'p.description',
                    'p.work_schedule',
                ]);

            if (isset($filters['name']) && !empty($filters['name'])) {
                $partners = $partners->where('p.name', 'ILIKE', '%' . $filters['name'] . '%');
            }

            $partners = $partners->paginate(32);

            $partners->getCollection()->transform(function ($partner) {
                $partner->logo_url = Storage::disk('s3')->url($partner->logo);
                $contacts = Contact::query()
                    ->where('publisher_id', $partner->id)
                    ->get()
                    ->pluck("value", "key");
                $partner->contacts = $contacts;
                return $partner;
            });

            return Helpers::reponse(true, $partners, 200, [], true);
        } catch (Exception $e) {
            return Helpers::reponse(false, ['message' => 'Falha ao consultar informações.'], 500, [], true, $e);
        }
    }

}
