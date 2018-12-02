<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;

class SendMessageMiddlewareFactory
{
    /** @var string */
    private $busName;

    public function __construct(string $busName = 'messenger.bus.default')
    {
        $this->busName = $busName;
    }

    public function __invoke(ContainerInterface $container) : MiddlewareInterface
    {
        $factory = new ContainerSendersLocatorFactory($this->busName);

        return new SendMessageMiddleware($factory($container));
    }
}
