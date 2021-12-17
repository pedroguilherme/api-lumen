<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class Role
{

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$roles
     * @return mixed
     * @throws CustomException
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $users_type = Config::get('constant.user_type');

        try {
            foreach ($roles as $role) {
                if (Auth::user()->type == $users_type[$role]) {
                    return $next($request);
                }
            }

            return Helpers::reponse(false, null, 401, Config::get('errors.unauthorized'));
        } catch (\Exception $e) {
            return Helpers::reponse(false, null, 500, Config::get('errors.internal'));
        }
    }
}
