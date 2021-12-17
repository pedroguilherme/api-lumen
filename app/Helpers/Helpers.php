<?php

namespace App\Helpers;

use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Exception;
use Sentry\Laravel\Facade as Sentry;
use Sentry\State\Scope;

use function Sentry\configureScope;

class Helpers
{
    /**
     * Default Response API
     *
     * @param bool $success
     * @param $resources
     * @param int $httpCode
     * @param array $errors
     * @param bool $jsonResponse
     * @param Exception|null $e
     * @return JsonResponse
     * @throws CustomException
     */
    public static function reponse(
        bool $success,
        $resources,
        int $httpCode = 200,
        array $errors = [],
        bool $jsonResponse = true,
        Exception $e = null
    ) {
        $response = [
            "success" => $success,
            "code" => $httpCode,
            "resources" => $resources,
        ];

        if (!empty($errors)) {
            $response["errors"] = isset($errors["type"]) ? [$errors] : $errors;
        }

        if (!empty($e)) {
            Helpers::logCodeError($e);
            Sentry::captureException($e);
        }

        if ($jsonResponse) {
            return response()->json($response, $httpCode);
        } else {
            if ($success && empty($errors)) {
                return $resources;
            } else {
                $message = '';
                foreach ($response["errors"] as $error) {
                    $message .= '| ';
                    if (isset($error['type'])) {
                        $message .= $error['type'];
                    }
                    if (isset($error['data'])) {
                        $message .= ' :: ' . $error['data'];
                    }
                    $message .= ' |';
                }
                configureScope(function (Scope $scope): void {
                    $user = Auth::user();
                    if (!empty($user)) {
                        $scope->setUser($user->toArray());
                    }
                });
                throw new CustomException($message, 0, null, $errors);
            }
        }
    }

    /**
     * Make a array off errors for response
     *
     * @param MessageBag $errors
     * @return JsonResponse
     * @throws Exception
     */
    public static function reponseWithValidationError(MessageBag $errors)
    {
        $arrayErrors = $errors->toArray();

        $errorsResponse = ["fields" => array_keys($arrayErrors), "messages" => array_values($arrayErrors)];

        return Helpers::reponse(false, null, 406, [["type" => "VALIDATION_ERROR", "data" => $errorsResponse]]);
    }

    /**
     * Log the erro as Exception
     *
     * @param Exception $e
     */
    public static function logCodeError(\Exception $e)
    {
        Log::error($e->getFile() . "::" . $e->getLine() . " | " . $e->getMessage());
    }

    /**
     * @param $str
     * @param string $replaceOthers
     * @return string|string[]|null
     */
    public static function sanitizeString($str, string $replaceOthers = '_')
    {
        $str = preg_replace('/[áàãâä]/ui', 'a', $str);
        $str = preg_replace('/[éèêë]/ui', 'e', $str);
        $str = preg_replace('/[íìîï]/ui', 'i', $str);
        $str = preg_replace('/[óòõôö]/ui', 'o', $str);
        $str = preg_replace('/[úùûü]/ui', 'u', $str);
        $str = preg_replace('/[ç]/ui', 'c', $str);
        $str = preg_replace('/[^a-z0-9]/i', $replaceOthers, $str);
        $str = preg_replace('/_+/', $replaceOthers, $str);
        return $str;
    }


}
