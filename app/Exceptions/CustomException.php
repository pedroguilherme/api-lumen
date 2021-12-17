<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Auth;
use Sentry\State\Scope;

use Throwable;

use function Sentry\configureScope;

class CustomException extends Exception
{
    /**
     * @var array
     */
    private array $customError;

    /**
     * CustomException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param array $customError
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, array $customError = [])
    {
        parent::__construct($message, $code, $previous);
        $this->customError = $customError;
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        configureScope(function (Scope $scope): void {
            $user = Auth::user();
            if (!empty($user)) {
                $scope->setUser($user->toArray());
            }
        });
        app('sentry')->captureException($this);
    }


    public function getErrorsArray()
    {
        return $this->customError;
    }
}
