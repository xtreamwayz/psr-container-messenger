<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerConsumerCommandFactory
{
    public function __invoke(ContainerInterface $container) : MessengerConsumerCommand
    {
        return new MessengerConsumerCommand(
            $container->get(MessageBusInterface::class),
            $container
        );
    }
}
