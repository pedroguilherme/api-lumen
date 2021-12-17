<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Mail\OfferCopy;
use App\Mail\OfferFinancing;
use App\Mail\OfferProposal;
use App\Mail\OfferSeePhone;
use App\Mail\OfferSellVehicle;
use App\Models\Offer;
use App\Models\Publisher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Exception;

class OfferService
{
    private Offer $model;

    /**
     * OfferService constructor.
     * @param Offer $model
     */
    public function __construct(Offer $model)
    {
        $this->model = $model;
    }


    /**
     * Resource search a database using filters
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse
     * @throws Exception
     */
    public function search($data, bool $jsonResponse = true)
    {
        try {
            //Apply filters
            $resources = $this->model->applyShowWith($data);

            $filter = false;
            if (isset($data['in']) && !empty($data['in'])) {
                $filter = true;
                $resources = $resources->where('created_at', '>=', $data['in']);
            }
            if (isset($data['until']) && !empty($data['until'])) {
                $filter = true;
                $resources = $resources->where('created_at', '<=', ($data['until'].' 23:59:59'));
            }

            if (isset($data["all"]) && $data["all"] == "true") {
                if (!$filter) {
                    $resources = $resources->where('created_at', '>=', Carbon::now()->subDays(30)->format('Y-m-d'))
                        ->where('created_at', '<=', Carbon::now());
                }
                $resources = $resources->get();
            } else {
                $resources = $resources->paginate(Config::get('constant.pagination'));
            }

            return Helpers::reponse(true, $resources, 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource search a database using filters
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function show($data, bool $jsonResponse = true)
    {
        try {
            // Get resource in database
            $resource = $this->model::query()
                ->with('vehicle')
                ->with('version');

            if (isset($data['publisher_id']) && !empty($data['publisher_id'])) {
                $resource = $resource->where('publisher_id', $data['publisher_id']);
            } else {
                if (isset($data['origin']) && empty($data['publisher_id'])) {
                    return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
                }
            }

            $resource = $resource->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            $resource->read = isset($data['origin']) ? true : $resource->read;
            $resource->save();

            return Helpers::reponse(true, $resource, 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * @param array $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function financing(array $data)
    {
        try {
            DB::beginTransaction();

            $resource = $this->model->fill($data);

            $resource->type = 'F';

            if ($resource->save()) {
                DB::commit();

                // Envia confirmação para o cliente
                Mail::to([$resource->client_name => $resource->client_email])->queue(new OfferCopy($data));

                // Envia confirmação para o anunciante
                $listEmails = $resource->publisher->emails->where('key', 'offerEmail');
                if ($listEmails->isEmpty()) {
                    Mail::to([$resource->publisher->user])->queue(new OfferFinancing($resource));
                } else {
                    foreach ($listEmails as $email) {
                        Mail::to([$resource->publisher->name => $email->value])->queue(new OfferFinancing($resource));
                    }
                }

                $resource->email_sended = true;
                $resource->save();

                return Helpers::reponse(true, $resource);
            } else {
                throw new Exception('Falha ao atualizar no banco de dados');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    /**
     * @param array $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function proposal(array $data)
    {
        try {
            DB::beginTransaction();

            $resource = $this->model->fill($data);
            $resource->type = 'P';

            if ($resource->save()) {
                DB::commit();

                // Envia confirmação para o cliente
                Mail::to([$resource->client_name => $resource->client_email])->queue(new OfferCopy($data));

                // Envia confirmação para o anunciante
                $listEmails = $resource->publisher->emails->where('key', 'offerEmail');
                if ($listEmails->isEmpty()) {
                    Mail::to($resource->publisher->user)->queue(new OfferProposal($resource));
                } else {
                    foreach ($listEmails as $email) {
                        Mail::to([$resource->publisher->name => $email->value])->queue(new OfferProposal($resource));
                    }
                }

                return Helpers::reponse(true, $resource);
            } else {
                throw new Exception('Falha ao atualizar no banco de dados');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    /**
     * @param array $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function seePhone(array $data)
    {
        try {
            DB::beginTransaction();

            $resource = $this->model->fill($data);
            $resource->type = 'S';
            $resource->client_init_message = 'Pedi para visualizar seu número para entrar em contato!';

            if ($resource->save()) {
                DB::commit();

                // Envia para o anunciante
                $listEmails = $resource->publisher->emails->where('key', 'offerEmail');
                if ($listEmails->isEmpty()) {
                    Mail::to($resource->publisher->user)->queue(new OfferSeePhone($resource));
                } else {
                    foreach ($listEmails as $email) {
                        Mail::to([$resource->publisher->name => $email->value])->queue(new OfferSeePhone($resource));
                    }
                }

                return Helpers::reponse(true, $resource);
            } else {
                throw new Exception('Falha ao atualizar no banco de dados');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    /**
     * @param array $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function sellVehicle(array $data)
    {
        try {
            DB::beginTransaction();

            $publishers = Publisher::query()
                ->where('type', 'J')
                ->whereIn('payment_situation', ['paid', 'free'])
                ->get();

            foreach ($publishers as $publisher) {
                $resource = new Offer();
                $resource->fill($data);
                $resource->publisher_id = $publisher->id;
                $resource->type = 'V';
                $resource->client_init_message = ($data['client_init_message'] ?? 'Estou interessado em vender meu veículo, por favor entre em contato caso haja interesse.');

                if ($resource->save()) {
                    // Envia para o lojistas
                    $listEmails = $resource->publisher->emails->where('key', 'offerEmail');
                    if ($listEmails->isEmpty()) {
                        Mail::to($resource->publisher->user)->queue(new OfferSellVehicle($resource));
                    } else {
                        foreach ($listEmails as $email) {
                            Mail::to([$resource->publisher->name => $email->value])->queue(new OfferSellVehicle($resource));
                        }
                    }
                } else {
                    throw new Exception('Falha ao atualizar no banco de dados');
                }
            }

            DB::commit();
            Mail::to([$data["client_name"] => $data["client_email"]])->queue(new OfferCopy($data));
            return Helpers::reponse(true, []);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    /**
     * @param array $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function banner(array $data)
    {
        try {
            DB::beginTransaction();

            $resource = $this->model->fill($data);
            $resource->type = 'B';
            $resource->client_email = '';
            $resource->client_init_message = 'Um cliente clicou no Banner de sua loja!';

            if ($resource->save()) {
                DB::commit();
                return Helpers::reponse(true, $resource);
            } else {
                throw new Exception('Falha ao atualizar no banco de dados');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }
}
