<?php

namespace Francerz\WebappCommons\Middlewares;

use ErrorException;
use Exception;
use Francerz\Console\BackColors;
use Francerz\Console\ForeColors;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DebugMiddleware implements MiddlewareInterface
{
    public const ENV_PRODUCTION = 'production';
    public const ENV_DEVELOPER = 'developer';

    private $responseFactory;
    private $environment = self::ENV_PRODUCTION;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function setEnvironment(string $environment)
    {
        $this->environment = $environment;
    }

    private function initDebugContext()
    {
        if (in_array($this->environment, [self::ENV_DEVELOPER])) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
    }

    private function setErrorHandler()
    {
        set_error_handler(function ($errno, $error, $errfile, $errline) {
            // if (php_sapi_name() !== 'cli-server') {
            //     return;
            // }
            throw new ErrorException("{$errno}: {$error} ({$errfile}:{$errline})", $errno);
        });
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->initDebugContext();
            $this->setErrorHandler();
            return $handler->handle($request);
        } catch (Exception $ex) {
            $errorString = get_class($ex) . ': ' . $ex->getMessage();
            $response = $this->responseFactory->createResponse(500);
            if (in_array($this->environment, [self::ENV_DEVELOPER])) {
                $response = $response->withHeader('Content-Type', 'text/plain');
                $response->getBody()->write($errorString);
            }
            if (php_sapi_name() === 'cli-server') {
                error_log(
                    BackColors::RED .
                    ForeColors::WHITE .
                    $errorString .
                    BackColors::DEFAULT .
                    ForeColors::DEFAULT
                );
            }
            return $response;
        }
    }
}
