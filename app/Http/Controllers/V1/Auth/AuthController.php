<?php

namespace App\Http\Controllers\V1\Auth;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected array $rulesValidator = [
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ];

    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException|CustomException
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rulesValidator);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        if (!$token = auth('api')->attempt($validator->validated())) {
            return Helpers::reponse(false, null, 401, ["type" => "UNAUTHORIZED", "data" => __("auth.failed")]);
        }

        return $this->returnToken($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     * @throws CustomException
     */
    public function refresh(): JsonResponse
    {
        return $this->returnToken(Auth::refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function userProfile(): JsonResponse
    {
        return response()->json(Auth::user());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     * @throws CustomException
     */
    protected function returnToken(string $token): JsonResponse
    {
        return Helpers::reponse(true, [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() . " minutes",
            'user' => Auth::user()
        ]);
    }


}
