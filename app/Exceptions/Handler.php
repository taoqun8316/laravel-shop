<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    //invalidate exception
    protected function invalidJson($request, ValidationException $exception)
    {
        return $this->error([
            $exception->errors()
        ], $exception->status, $exception->getMessage());
    }

    public function handle($request, Exception $e)
    {
        if ($e instanceof ApiException) {
            $result = [
                "msg"    => $e->getMessage(),
                "data"   => '',
                "status" => 0
            ];
            return $this->error($result, $e->getCode());
        }

        if($request->is('api/*')){
            $response = [];
            $error = $this->convertExceptionToResponse($e);
            $response['status'] = $error->getStatusCode();
            $response['msg'] = 'something error';

            if(config('app.debug')) {
                $response['msg'] = empty($e->getMessage()) ? 'something error' : $e->getMessage();
                if($error->getStatusCode() >= 500) {
                    if(config('app.debug')) {
                        $response['trace'] = $e->getTraceAsString();
                        $response['code'] = $e->getCode();
                    }
                }
            }
            $response['data'] = [];

            return $this->error(222, $error->getStatusCode());
        }

        return parent::render($request, $e);
    }

}
