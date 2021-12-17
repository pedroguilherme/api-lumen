<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\Publisher;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

class ExternalAuth
{
    /**
     * The authentication guard factory instance.
     *
     * @var Auth
     */
    protected Auth $auth;

    /**
     * Create a new middleware instance.
     *
     * @param Auth $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return mixed
     * @throws CustomException
     */
    public function handle(Request $request, Closure $next, string $guard = null)
    {
        $apiToken = trim($request->header('X-ApiToken'));

        if (!empty($apiToken)) {
            $publisher = Publisher::where('api_token', $apiToken)->first();
            if (!empty($publisher)) {
                if ($publisher->type == 'J' && $publisher->access_status == 'full') {
                    $user = $publisher->user;
                    if (!empty($user)) {
                        $this->auth->guard($guard)->setUser($user);
                        return $next($request);
                    }
                }
            }
        }

        return Helpers::reponse(false, null, 401, Config::get('errors.unauthorized'));
    }
}
