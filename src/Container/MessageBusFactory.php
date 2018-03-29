<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use XtreamLabs\Expressive\Messenger\Exception\InvalidConfigException;

class MessageBusFactory
{
    public function __invoke(ContainerInterface $container) : MessageBusInterface
    {
        $config      = $container->has('config') ? $container->get('config') : [];
        $config      = $config['messenger'] ?? [];
        $middlewares = $config['middleware'] ?? [];

        $stack = [];
        foreach ($middlewares as $middleware) {
            $stack[] = $container->get($middleware);
        }

        if (empty($stack)) {
            throw new InvalidConfigException(
                'Without middleware, messenger does not do anything!'
            );
        }

        return new MessageBus($stack);
    }
}
