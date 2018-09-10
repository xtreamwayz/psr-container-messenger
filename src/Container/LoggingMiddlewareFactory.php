<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Middleware\LoggingMiddleware;

class LoggingMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : LoggingMiddleware
    {
        return new LoggingMiddleware(
            $container->get(LoggerInterface::class)
        );
    }
}
