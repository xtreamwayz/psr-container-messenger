<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test\Fixtures;

class DummyCommandHandler
{
    public function __invoke(DummyCommand $command) : void
    {
    }
}
