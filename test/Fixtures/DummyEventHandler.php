<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test\Fixtures;

class DummyEventHandler
{
    public function __invoke(DummyEvent $event) : void
    {
    }
}
