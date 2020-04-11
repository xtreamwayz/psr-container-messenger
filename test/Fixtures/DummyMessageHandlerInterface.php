<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test\Fixtures;

interface DummyMessageHandlerInterface
{
    public function __invoke(DummyMessageInterface $message) : void;
}
