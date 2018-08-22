<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Command;

use Psr\Container\ContainerInterface;

class CommandQueueWorkerFactory
{
    public function __invoke(ContainerInterface $container) : CommandQueueWorker
    {
        return new CommandQueueWorker(
            $container->get('messenger.bus.command'),
            $container
        );
    }
}
