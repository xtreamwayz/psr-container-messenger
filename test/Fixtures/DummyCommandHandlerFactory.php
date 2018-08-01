<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger\Fixtures;

use Psr\Container\ContainerInterface;

class DummyCommandHandlerFactory
{
    public function __invoke(ContainerInterface $container) : DummyCommandHandler
    {
        return new DummyCommandHandler();
    }
}
