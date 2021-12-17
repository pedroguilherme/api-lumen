<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Helpers\HelpersImage;
use App\Mail\DeactivatePublisher;
use App\Mail\UpdatePlanPublisher;
use App\Models\CreditCard;
use App\Models\Plan;
use App\Models\Publisher;
use App\Traits\DefaultService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;

class PublisherService implements DefaultServiceContracts
{
    use DefaultService;

    protected ContactService $contactService;
    protected UserService $userService;
    protected ChargesService $chargeService;
    protected CreditCardService $creditCardService;

    /**
     * PublisherService constructor.
     * @param Publisher $model
     * @param ContactService $contactService
     * @param UserService $userService
     * @param ChargesService $chargeService
     * @param CreditCardService $creditCardService
     */
    public function __construct(
        Publisher $model,
        ContactService $contactService,
        UserService $userService,
        ChargesService $chargeService,
        CreditCardService $creditCardService
    ) {
        $this->model = $model;
        $this->contactService = $contactService;
        $this->userService = $userService;
        $this->chargeService = $chargeService;
        $this->creditCardService = $creditCardService;
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
            //Apply filters
            $paymentSituationWait = !empty($data['payment_situation']) && $data['payment_situation'] == 'waiting_payment';
            $data = ($paymentSituationWait ? Arr::except($data, ['payment_situation']) : $data);
            $resources = $this->model->applyShowWith($data);

            if ($paymentSituationWait) {
                $resources->whereIn('payment_situation', ['first_waiting_payment', 'waiting_payment']);
            }

            if (isset($data["all"]) && $data["all"] == "true") {
                $resources = $resources->get();
            } else {
                $resources = $resources->paginate(Config::get('constant.pagination'));
            }

            return Helpers::reponse(true, $resources, 200, [], $jsonResponse);
        } catch (Exception $e) {
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
    public function show($data, $jsonResponse = true)
    {
        try {
            // Get resource in database
            $resource = $this->model->applyShowWith()->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            $resource->setRelation('contacts', $resource->contacts->pluck("value", "key"));

            return Helpers::reponse(true, $resource, 200, [], $jsonResponse);
        } catch (Exception $e) {
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
     * @return Publisher|JsonResponse
     * @throws Exception
     */
    public function store($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();

            $resource = $this->model->fill($data);

            if (!empty($data['free_active_date'])) {
                $resource->free_active_date = Carbon::createFromFormat('d/m/Y', $data['free_active_date'])
                    ->format('Y-m-d');
                $resource->free = intval(($data['free'] ?? null));
            }

            if ($resource->type == 'F') {
                $resource->access_status = 'full';
            }

            // Gera token de API da loja
            $resource->api_token = hash('sha256', ($resource->cpf_cnpj . '-' . $resource->name));

            if ($resource->save()) {
                // Checa se há carencia cadastrada se tiver faz o update
                $this->chargeService->updateFreeActive($resource);

                // Insert Array Contacts
                $this->storeContactsByArray($data['contacts'], $resource);

                // Insert User
                $dataUser = Arr::only($data, ['password', 'name']);
                $dataUser['email'] = mb_strtolower($data['login']);
                $dataUser['publisher_id'] = $resource->id;
                $dataUser['type'] = ($resource->type == 'J' ? 'S' : 'P');
                $this->userService->store($dataUser, false);

                DB::commit();

                if ($data['origin'] == 'site') {
                    // FORMAS DE PAGAMENTO
                    if ($resource->type == 'J') {
                        $resource->refresh();
                        if ($data['payment_method'] == 'CC') {
                            $credit_card = [
                                'default' => true,
                                'publisher_id' => $resource->id,
                                'holder_name' => $data['cc_holder_name'],
                                'number' => $data['cc_number'],
                                'expiration_date' => $data['cc_expiration_date'],
                                'cvv' => $data['cc_cvv'],
                            ];
                            $creditCard = $this->creditCardService->store($credit_card, false);
                            if (($creditCard instanceof CreditCard) and empty($creditCard->encrypted)) {
                                throw new Exception('Falha ao gravar Cartão no banco de dados');
                            } else {
                                if (!($creditCard instanceof CreditCard)) {
                                    throw $creditCard;
                                }
                            }
                        }
                        $this->chargeService->checkAndCharge($resource);
                    }
                }

                return Helpers::reponse(true, $this->show($resource, false), 201, [], $jsonResponse);
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
     * Resource store a database.
     * Check if resource created exist actually, if exist return error with resource
     *
     * @param $data
     * @param bool $jsonResponse
     * @return Publisher|JsonResponse
     * @throws Exception
     */
    public function update($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();

            // Get resource in database
            $resource = $this->model->applyShowWith()->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            if (isset($data['free'])) {
                $resource->free = intval($data['free']);
            }

            if (isset($data['free_active_date'])) {
                if (!empty($data['free_active_date'])) {
                    $resource->free_active_date = Carbon::createFromFormat('d/m/Y', $data['free_active_date'])
                        ->format('Y-m-d');
                } else {
                    $resource->free_active_date = null;
                }
            }

            $resource = $resource->fill($data);

            if ($resource->type == 'F') {
                $resource->access_status = 'full';
            }

            // Em caso de recebimento da logo, faz o upload e atualiza o objeto de loja
            if (isset($data["logo"]) && !empty($data["logo"])) {
                $params = ['$publisher_id' => $resource->id];
                $path = HelpersImage::upload(Image::make($data["logo"]), 'logo_publisher', $resource->name, $params);

                if ($path === false) {
                    throw new Exception('Falha ao incluir ou atualizar logo.');
                }

                $resource->logo = $path;
            }

            if ($resource->save()) {
                // Checa se há carencia cadastrada se tiver faz o update
                $this->chargeService->updateFreeActive($resource);

                // Update contatcs
                if (isset($data['contacts'])) {
                    $this->updateContactsByArray($data['contacts'], $resource);
                }

                // Update user
                $dataUser = [
                    'id' => $resource->user->id,
                    'name' => $data['name'],
                    'email' => mb_strtolower($data['login']),
                    'type' => ($resource->type == 'J' ? 'S' : 'P'),
                    'password' => $data['password'] ?? null,
                ];
                $this->userService->update($dataUser, false);

                DB::commit();
                return Helpers::reponse(true, $this->show($resource, false), 200, [], $jsonResponse);
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
     * Resource destroy or restore a database.
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws CustomException
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

            if ($resource->payment_situation != 'canceled') {
                $resource->deleted_reason = $data['reason'];
                $resource->access_status = 'limited';
                $resource->payment_situation = 'canceled';
                if (!$resource->save()) {
                    throw new Exception('Falha ao atualizar motivo no banco de dados');
                }

                // Busca por onde que veio a desativação (publisher ou admin)
                $origin = ($data['origin'] ?? 'admin');

                $vehicles = $resource->vehicles;
                foreach ($vehicles as $vehicle) {
                    $vehicle->delete();
                }

                // Em caso de Desativação envia e-mail de confirmação
                $mailList = collect([]);
                // Caso a origin for da anunciante, enviar e-mail para Empresa
                if ($origin == 'publisher') {
                    $mailList = $this->userService->search(['type' => 'A', 'active' => true, 'all' => true], false);
                }

                // Envia para o e-mail da anunciante desativada idependente da origin de desativação
                $mailList->push($resource->user);
                Mail::to($mailList)->queue(new DeactivatePublisher($resource, $origin));
            } else {
                $resource->deleted_reason = $data['reason'];
                $resource->access_status = 'limited';
                $resource->payment_situation = 'first';
                $resource->payment_nextcheck = null;
                $resource->paid_in = null;
                if (!$resource->save()) {
                    throw new Exception('Falha ao atualizar motivo no banco de dados');
                }
            }

            DB::commit();
            return Helpers::reponse(true, $resource, 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Show plan for Publisher
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse
     * @throws CustomException
     */
    public function showPlan($data, bool $jsonResponse = true)
    {
        try {
            // Get resource in database
            $publisher = $this->show($data, false);
            $plan = $publisher->plan;

            if ($publisher->type == 'J') {
                // Busca a quantidade de anuncios DISPONIVEIS por cada destaque
                $vehicles = $publisher->vehicles;
                $used = $vehicles->groupBy("spotlight")
                    ->map(function ($item) {
                        return $item->count();
                    });

                $plan->available = [
                    "normal" => intval($plan->normal - ($used["N"] ?? 0)),
                    "silver" => intval($plan->silver - ($used["S"] ?? 0)),
                    "gold" => intval($plan->gold - ($used["G"] ?? 0)),
                    "diamond" => intval($plan->diamond - ($used["D"] ?? 0)),
                ];
            }

            return Helpers::reponse(true, $plan, 200, [], $jsonResponse);
        } catch (Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Show plan for Publisher
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse
     * @throws CustomException
     */
    public function updatePlan($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();

            // Get resource in database
            $resource = $this->model->applyShowWith()->find($data["id"]);

            // Check Resource
            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            $makeInvoice = false;
            $oldPlan = $resource->plan;
            if (!in_array($resource->payment_situation, ['refused', 'free', 'unpaid', 'first'])) {
                $resource->future_plan_id = $data["plan_id"];
            } else {
                $resource->future_plan_id = null;
                $resource->plan_id = $data["plan_id"];
                $makeInvoice = true;
            }

            if ($resource->save()) {
                // Refresh Plan Relationship
                $resource->unsetRelation('plan');
                $resource->unsetRelation('futurePlan');

                // Envia e-mail de confirmação para os Admins da Empresa.
                $mailList = $this->userService->search(['type' => 'A', 'active' => true, 'all' => true], false);
                $newPlan = Plan::find($data["plan_id"]);
                Mail::to($mailList)->queue(new UpdatePlanPublisher($resource, $newPlan, $oldPlan));

                Mail::to(['Usuário Teste' => 'usuario_teste@teste.com.br'])
                    ->queue(new UpdatePlanPublisher($resource, $newPlan, $oldPlan));

                DB::commit();

                if ($makeInvoice) {
                    $billing = $resource->lastBilling;
                    $billing->status = 'unpaid';
                    $billing->payed_at = null;
                    $billing->save();
                    $this->chargeService->makeInvoice($resource);
                }

                return Helpers::reponse(true, $this->showPlan($resource, false), 200, [], $jsonResponse);
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
     * Insere contatos a partir de um array utilizando o ContactService
     *
     * @param array $contacts
     * @param Publisher $publisher
     * @return bool
     * @throws Exception
     */
    private function storeContactsByArray(array $contacts, Publisher $publisher): bool
    {
        foreach ($contacts as $contact) {
            if (Arr::has($contact, ['key', 'value']) && !empty($contact['key']) && !empty($contact['value'])) {
                $contact = $this->contactService->store(Arr::set($contact, 'publisher_id', $publisher->id), false);
                if ($contact === false) {
                    throw new Exception('Falha ao gravar (contact) no banco de dados');
                }
            }
        }
        return true;
    }

    /**
     * Insere contatos a partir de um array utilizando o ContactService
     *
     * @param array $contacts
     * @param Publisher $publisher
     * @return bool
     * @throws Exception
     */
    private function updateContactsByArray(array $contacts, Publisher $publisher)
    {
        $publisher->contacts()->forceDelete();
        $this->storeContactsByArray($contacts, $publisher);
        return true;
    }
}
