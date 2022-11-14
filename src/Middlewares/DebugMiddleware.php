<?php

namespace Francerz\WebappCommons\Middlewares;

use ErrorException;
use Exception;
use Francerz\Console\BackColors;
use Francerz\Console\ForeColors;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DebugMiddleware implements MiddlewareInterface
{
    private function setErrorHandler()
    {
        set_error_handler(function ($errno, $error, $errfile, $errline) {
            if (php_sapi_name() !== 'cli-server') {
                return;
            }
            throw new ErrorException("{$errno}: {$error} ({$errfile}:{$errline})", $errno);
        });
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->setErrorHandler();
            return $handler->handle($request);
        } catch (Exception $ex) {
            if (php_sapi_name() === 'cli-server') {
                error_log(
                    BackColors::RED . ForeColors::WHITE .
                    'Exception ' . get_class($ex) . ": {$ex->getMessage()}" .
                    BackColors::DEFAULT . ForeColors::DEFAULT
                );
            }
        }
    }
}
