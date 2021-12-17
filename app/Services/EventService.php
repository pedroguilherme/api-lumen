<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Helpers\HelpersSite;
use App\Models\Offer;
use App\Models\Publisher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Support\Facades\DB;

class EventService
{

    /**
     * MongoDB Collection
     * @var string
     */
    private string $collection = 'events';

    /**
     * Resource search a database using filters
     *
     * @param $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function store($data)
    {
        try {
            $result = DB::connection("mongodb")
                ->collection($this->collection)
                ->insert([
                    'type' => $data['type'],
                    'publisher_id' => $data['publisher_id'],
                    'vehicle_id' => ($data['vehicle_id'] ?? null),
                    'date' => Carbon::now()->format("Y-m-d\TH:i:s\Z"),
                ]);

            return Helpers::reponse(true, $result);
        } catch (Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }

    /**/
    public function search($data)
    {
        $events = $this->getEvents($data);
        $offers = $this->getOffers($data);

        // GENERAL
        $countLeadByDay = []; // Count com dias com mais leads
        $countBannerClick = 0; // Count de clicks no banner

        // VEHICLE
        $countVehicleView = 0; //  Count ao visualizar anuncio
        $countVehicleWhatsApp = 0; //  Count no whats por anúncio
        $countVehiclePhone = 0; // Count no telefone por anúncio
        $countVehicleMostViews = []; // Count no view de anuncio
        $countVehicleMostLeads = []; // Count de leads por veiculo

        // PARTNERS
        $countPartnerLeads = []; // Count de leads por empresa
        $countPartnerWhatsApp = 0; // Count no whats por empresa
        $countPartnerPhone = 0; // Count no telefone por empresa
        $countPartnerView = 0; // Count ao visualizar estoque

        // OFFERS
        $countProposal = 0;
        $countFinancing = 0;
        $countSeePhone = 0;

        // Control
        $vehicles = [];
        $publishers = [];

        // Make Data Events
        foreach ($events as $event) {
            $date = Carbon::parse($event->date);
            $formatDate = $date->format('Y-m-d');
            switch ($event->type) {
                case 'VEHICLE_WHATSAPP':
                    $countVehicleWhatsApp++;
                    break;
                case 'VEHICLE_PHONE':
                    $countVehiclePhone++;
                    break;
                case 'VEHICLE_VIEW':
                    $countVehicleView++;
                    break;
                case 'PARTNERS_WHATSAPP':
                    $countPartnerWhatsApp++;
                    break;
                case 'PARTNERS_PHONE':
                    $countPartnerPhone++;
                    break;
                case 'PARTNERS_VIEW':
                    $countPartnerView++;
                    break;
                case 'BANNER_CLICK':
                    $countBannerClick++;
                    break;
            }

            if (isset($event->publisher_id) && !empty($event->publisher_id)) {
                if (array_search($event->type, [
                        'VEHICLE_WHATSAPP',
                        'VEHICLE_PHONE',
                        'PARTNERS_WHATSAPP',
                        'PARTNERS_PHONE',
                        'BANNER_CLICK'
                    ]) !== false) {
                    if (isset($countLeadByDay[$formatDate])) {
                        $countLeadByDay[$formatDate] = $countLeadByDay[$formatDate] + 1;
                    } else {
                        $countLeadByDay[$formatDate] = 1;
                    }
                    if (isset($countPartnerLeads[$event->publisher_id])) {
                        $countPartnerLeads[$event->publisher_id] = $countPartnerLeads[$event->publisher_id] + 1;
                    } else {
                        $countPartnerLeads[$event->publisher_id] = 1;
                    }
                    if (isset($event->vehicle_id) && !empty($event->vehicle_id)) {
                        if (isset($countVehicleMostLeads[$event->vehicle_id])) {
                            $countVehicleMostLeads[$event->vehicle_id] = $countVehicleMostLeads[$event->vehicle_id] + 1;
                        } else {
                            $countVehicleMostLeads[$event->vehicle_id] = 1;
                        }
                    }
                }
                $publishers[$event->publisher_id] = !isset($publishers[$event->publisher_id]) ? 1 : $publishers[$event->publisher_id] + 1;
            }

            if (isset($event->vehicle_id) && !empty($event->vehicle_id)) {
                if (array_search($event->type, ['PARTNERS_VIEW', 'VEHICLE_VIEW']) !== false) {
                    if (isset($countVehicleMostViews[$event->vehicle_id])) {
                        $countVehicleMostViews[$event->vehicle_id]++;
                    } else {
                        $countVehicleMostViews[$event->vehicle_id] = 1;
                    }
                }
                $vehicles[$event->vehicle_id] = !isset($vehicles[$event->vehicle_id]) ? 1 : $vehicles[$event->vehicle_id] + 1;
            }
        }

        // Make Data Offers
        foreach ($offers as $offer) {
            $date = Carbon::parse($offer->created_at);
            $formatDate = $date->format('Y-m-d');
            switch ($offer->type) {
                case 'P':
                    $countProposal++;
                    break;
                case 'F':
                    $countFinancing++;
                    break;
                case 'S':
                    $countSeePhone++;
                    break;
            }

            if (isset($offer->vehicle_id) && !empty($offer->vehicle_id)) {
                if (isset($countVehicleMostLeads[$offer->vehicle_id])) {
                    $countVehicleMostLeads[$offer->vehicle_id] = $countVehicleMostLeads[$offer->vehicle_id] + 1;
                } else {
                    $countVehicleMostLeads[$offer->vehicle_id] = 1;
                }
                $vehicles[$offer->vehicle_id] = !isset($vehicles[$offer->vehicle_id]) ? 1 : $vehicles[$offer->vehicle_id] + 1;
            }

            if (isset($offer->publisher_id) && !empty($offer->publisher_id)) {
                if (isset($countLeadByDay[$formatDate])) {
                    $countLeadByDay[$formatDate] = $countLeadByDay[$formatDate] + 1;
                } else {
                    $countLeadByDay[$formatDate] = 1;
                }
                if (isset($countPartnerLeads[$offer->publisher_id])) {
                    $countPartnerLeads[$offer->publisher_id] = $countPartnerLeads[$offer->publisher_id] + 1;
                } else {
                    $countPartnerLeads[$offer->publisher_id] = 1;
                }
                $publishers[$offer->publisher_id] = !isset($publishers[$offer->publisher_id]) ? 1 : $publishers[$offer->publisher_id] + 1;
            }
        }

        arsort($countLeadByDay);
        arsort($countVehicleMostViews);
        arsort($countVehicleMostLeads);
        arsort($countPartnerLeads);

        $vehicles = DB::table('vehicles as v')
            ->select('v.id', 'v.plate', 'v.publisher_id', 'b.name as brand', 'm.name as model',
                'vs.name as version', 'f.path as image')
            ->join('brands as b', 'b.id', '=', 'v.brand_id')
            ->join('models as m', 'm.id', '=', 'v.model_id')
            ->join('versions as vs', 'vs.id', '=', 'v.version_id')
            ->leftJoin('files as f', function ($join) {
                $join->on('v.id', '=', 'f.vehicle_id')
                    ->where('f.order', '=', 0);
            })
            ->whereIn('v.id', array_keys($vehicles))
            ->get()
            ->transform(function ($vehicle) {
                $vehicle->image = HelpersSite::makeImageURL($vehicle);
                return $vehicle;
            });

        $publishers = Publisher::withTrashed()
            ->whereIn('id', array_keys($publishers))
            ->select(['id', 'name'])
            ->get()
            ->makeHidden(['logo_url', 'free_active_date_format']);

        return Helpers::reponse(true, [
            'general' => [
                'countLeadByDay' => $countLeadByDay,
                'countBannerClick' => $countBannerClick,
            ],
            'vehicles' => [
                'countVehicleView' => $countVehicleView,
                'countVehicleWhatsApp' => $countVehicleWhatsApp,
                'countVehiclePhone' => $countVehiclePhone,
                'countVehicleMostViews' => $countVehicleMostViews,
                'countVehicleMostLeads' => $countVehicleMostLeads,
            ],
            'partners' => [
                'countPartnerLeads' => $countPartnerLeads,
                'countPartnerWhatsApp' => $countPartnerWhatsApp,
                'countPartnerPhone' => $countPartnerPhone,
                'countPartnerView' => $countPartnerView,
            ],
            'offers' => [
                'countProposal' => $countProposal,
                'countFinancing' => $countFinancing,
                'countSeePhone' => $countSeePhone,
            ],
            'lists' => [
                'vehicles' => $vehicles,
                'publishers' => $publishers,
            ]
        ]);
    }

    /**
     * @param $data
     * @return mixed
     */
    private function getEvents($data)
    {
        return DB::connection("mongodb")
            ->collection($this->collection)
            ->raw(function ($collection) use ($data) {
                $filter = [];
                if (isset($data['publisher_id']) && !empty($data['publisher_id'])) {
                    $filter['publisher_id'] = (int)$data['publisher_id'];
                }

                if (isset($data['in']) && !empty($data['in'])) {
                    $filter['date'] = [
                        '$gte' => Carbon::createFromFormat('Y-m-d', $data['in'])
                            ->format("Y-m-d\T00:00:00.000\Z")
                    ];
                }

                if (isset($data['until']) && !empty($data['until'])) {
                    $lte = Carbon::createFromFormat('Y-m-d', $data['until'])
                        ->addDay()
                        ->format("Y-m-d\T00:00:00.000\Z");

                    if (isset($filter['date']['$gte'])) {
                        $filter['date']['$lte'] = $lte;
                    } else {
                        $filter['date'] = ['$lte' => $lte];
                    }
                }

                if (!isset($filter['date'])) {
                    $filter['date'] = [
                        '$gte' => Carbon::now()->subDays(30)->format("Y-m-d\T00:00:00.000\Z"),
                        '$lte' => Carbon::now()->format("Y-m-d\T23:59:59.000\Z")
                    ];
                }

                return $collection->aggregate([
                    [
                        '$match' => $filter
                    ]
                ]);
            });
    }

    /**
     * @param int $vehicle_id
     * @param string $type
     * @return mixed
     */
    public function getVehicleEventsByType(int $vehicle_id, string $type = 'VEHICLE_VIEW')
    {
        return DB::connection("mongodb")
            ->collection($this->collection)
            ->raw(function ($collection) use ($vehicle_id, $type) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'vehicle_id' => $vehicle_id,
                            'type' => $type,
                        ]
                    ]
                ]);
            });
    }

    /**
     * @param int $vehicle_id
     * @return int
     */
    public function getClicksByVehicle(int $vehicle_id): int
    {
        $events = DB::connection("mongodb")
            ->collection($this->collection)
            ->raw(function ($collection) use ($vehicle_id) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'vehicle_id' => $vehicle_id,
                            'type' => 'VEHICLE_VIEW',
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => [
                                "vehicle_id" => '$vehicle_id',
                            ],
                            'count' => ['$sum' => 1]
                        ]
                    ],
                    [
                        '$sort' => [
                            'count' => -1
                        ]
                    ]
                ]);
            });

        $count = 0;
        foreach ($events as $event) {
            $count += $event->count;
        }

        return $count;
    }

    /**
     * @param $data
     * @return JsonResponse
     * @throws Exception
     */
    private function getOffers($data)
    {
        $offerService = new OfferService(new Offer());
        return $offerService->search(array_merge($data, ['all' => true]), false)->where('type', '!=', 'B');
    }
}
