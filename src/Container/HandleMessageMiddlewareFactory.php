<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Handler\Locator\ContainerHandlerLocator;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

class HandleMessageMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : MiddlewareInterface
    {
        $handlerLocator = new ContainerHandlerLocator($container);

        return new HandleMessageMiddleware($handlerLocator);
    }
}
