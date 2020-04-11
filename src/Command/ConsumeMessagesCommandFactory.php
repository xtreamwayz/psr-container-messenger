<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\RoutableMessageBus;

class ConsumeMessagesCommandFactory
{
    public function __invoke(ContainerInterface $container): ConsumeMessagesCommand
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $logger = $config['messenger']['logger'] ?? null;

        if ($container->has(EventDispatcherInterface::class)) {
            $dispatcher = $container->get(EventDispatcherInterface::class);
        } else {
            $dispatcher = new EventDispatcher();
        }

        return new ConsumeMessagesCommand(
            new RoutableMessageBus($container),
            $container,
            $dispatcher,
            $logger ? $container->get($logger) : null
        );
    }
}
