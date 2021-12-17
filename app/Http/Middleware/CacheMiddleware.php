<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @param int $ttl
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $ttl = 15)
    {
        try {
            // Camada do Laravel -> Perfomance

            // Camada do CloudFlare -> Economiza Dinheiro

            // Camada do Navegador -> Economiza Dinheiro e Perfomance

            if (env('APP_ENV') != 'local') {
                $nameCache = md5(json_encode($request->all(), JSON_INVALID_UTF8_IGNORE,
                        512) . $request->getRequestUri());

                if (Cache::has($nameCache)) {
                    return response()
                        ->json(Cache::get($nameCache))
                        ->header("Expires", Carbon::now()->addMinutes($ttl)->toRfc7231String())
                        ->header("Cache-control", "public, max-age=" . ($ttl * 60))
                        ->header('X-API-Cache', 'HIT');
                } else {
                    $return = $next($request);
                    if ($return->getStatusCode() == 200) {
                        $return = $return->header('X-API-Cache', 'MISS')
                            ->header("Expires", Carbon::now()->addMinutes($ttl)->toRfc7231String())
                            ->header("Cache-control", "public, max-age=" . ($ttl * 60));
                        Cache::put($nameCache, $return->original, ($ttl * 60));
                    }
                    return $return;
                }
            } else {
                $request->merge(["time_start" => microtime(true)]);
                $return = ($return ?? $next($request))->header('X-API-Cache', 'BYPASS');
                $time_end = microtime(true);
                $execution_time = ($time_end - $request->time_start);

                return $return->header("X-Exec-Time", $execution_time)
                    ->header("X-Env", env('APP_ENV'));
            }
        } catch (Exception $e) {
            return $next($request)
                ->header('X-API-Cache', 'ERROR')
                ->header('X-API-Message', $e->getMessage());
        }
    }
}
