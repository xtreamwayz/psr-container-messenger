<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\ContainerHandlerLocator;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\MiddlewareInterface;

class HandleMessageMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : MiddlewareInterface
    {
        $handlerLocator = new ContainerHandlerLocator($container);

        return new HandleMessageMiddleware($handlerLocator);
    }
}
