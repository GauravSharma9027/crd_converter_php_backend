<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Middleware\ErrorMiddleware;
use Throwable;

class ErrorHandler
{
    public static function register($app)
    {
        // Slim ka built-in error middleware
        $errorMiddleware = $app->addErrorMiddleware(true, true, true);

        // Custom error response
        $errorMiddleware->setDefaultErrorHandler(function (
            Request $request,
            Throwable $exception,
            bool $displayErrorDetails,
            bool $logErrors,
            bool $logErrorDetails
        ) use ($app) {
            $response = $app->getResponseFactory()->createResponse();

            $payload = [
                'success' => false,
                'message' => $exception->getMessage() ?: 'Internal Server Error'
            ];

            $response->getBody()->write(json_encode($payload));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($exception->getCode() ?: 500);
        });
    }
}
