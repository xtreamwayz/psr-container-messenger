<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\RoutableMessageBus;

class ConsumeMessagesCommandFactory
{
    public function __invoke(ContainerInterface $container) : ConsumeMessagesCommand
    {
        return new ConsumeMessagesCommand(
            new RoutableMessageBus($container),
            $container
        );
    }
}
