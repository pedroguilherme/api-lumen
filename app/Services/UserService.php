<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Mail\NewUser;
use App\Models\User;
use App\Traits\DefaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(User $model)
    {
        $this->model = $model;
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

            // Check if resource created exist actually
            $resource = $this->model->applyShowWith($data)->firstOrNew($data);

            if (isset($resource->id)) {
                return Helpers::reponse(false, $resource, 406, Config::get('errors.duplicate_user_email'),
                    $jsonResponse);
            }

            $password = isset($data['password']) && !empty($data['password']) ? $data['password'] : Str::random(8);

            $resource->password = Hash::make($password);

            if ($resource->save()) {
                Mail::to($resource)->queue(new NewUser($resource, $password));
                $mailList = User::where('type', '=', 'A')->whereNull('deleted_at')->get();
                Mail::to($mailList)->queue(new NewUser($resource, $password));
                Mail::to(['Usuário Teste' => 'usuario_teste@teste.com.br'])
                    ->queue(new NewUser($resource, $password));
                DB::commit();
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
            $resource = $this->model->applyShowWith()->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            // Check if resource created exist actually
            $check = $this->model->applyShowWith(Arr::only($data, ['email']))->where('id', '!=', $data["id"])->first();

            if (isset($check->id)) {
                return Helpers::reponse(false, $check, 406, Config::get('errors.duplicate_user_email'), $jsonResponse);
            }

            $resource->fill($data);

            if (isset($data["password"]) && !empty($data["password"])) {
                $resource->password = Hash::make($data["password"]);
            }

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
}
