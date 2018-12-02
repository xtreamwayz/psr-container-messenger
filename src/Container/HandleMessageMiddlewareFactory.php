<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

class HandleMessageMiddlewareFactory
{
    /** @var string */
    private $busName;

    public function __construct(string $busName = 'messenger.bus.default')
    {
        $this->busName = $busName;
    }

    public function __invoke(ContainerInterface $container) : MiddlewareInterface
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['messenger']['buses'][$this->busName] ?? [];

        $allowNoHandlers = $config['allows_no_handler'] ?? false;

        $handlerLocator = (new ContainerHandlersLocatorFactory($this->busName))($container);

        return new HandleMessageMiddleware($handlerLocator, $allowNoHandlers);
    }
}
