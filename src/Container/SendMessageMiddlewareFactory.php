<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Asynchronous\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Asynchronous\Routing\SenderLocator;
use Symfony\Component\Messenger\MiddlewareInterface;

class SendMessageMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : MiddlewareInterface
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['messenger'] ?? [];

        $senderLocator = new SenderLocator($container, $config['senders'] ?? []);

        return new SendMessageMiddleware($senderLocator);
    }
}
