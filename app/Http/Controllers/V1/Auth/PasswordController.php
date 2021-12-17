<?php

namespace App\Http\Controllers\V1\Auth;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Mail\ForgotPassword;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Namshi\JOSE\JWT;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class PasswordController extends Controller
{
    /**
     * Get the authenticated User.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        $data = $validator->validated();

        if ($validator != true) {
            return Helpers::reponse(false, [], 406, (array)$validator);
        } else {
            $user = User::where('email', $data['email'])->first();
            if (!empty($user)) {
                JWTAuth::factory()->setTTL(60);
                $token = JWTAuth::fromUser($user);
                Mail::to($user)->queue(new ForgotPassword($user, $token));
                return Helpers::reponse(true, 'Enviamos seu link de redefinição de senha por e-mail.');
            } else {
                return Helpers::reponse(false, [], 404, Config::get('errors.not_found'));
            }
        }
    }

    /**
     * Get the authenticated User.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        $data = $validator->validated();

        if ($validator != true) {
            return Helpers::reponse(false, [], 406, (array)$validator);
        } else {
            // Valida o token
            try {
                $tokenDecode = $data['token'];
                $token = JWTAuth::setToken($tokenDecode);
                if (($user = $token->authenticate())) {
                    $user->password = Hash::make($data['password']);
                    $user->save();
                    return Helpers::reponse(true, 'Sua senha foi redefinida.');
                } else {
                    throw new Exception('O token informado não é válido ou está expirado');
                }
            } catch (Exception $exception) {
                return Helpers::reponse(false, [], 401, Config::get('errors.token_invalid'));
            }
        }
    }

}
