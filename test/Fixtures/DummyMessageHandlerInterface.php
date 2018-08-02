<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger\Fixtures;

interface DummyMessageHandlerInterface
{
    public function __invoke(DummyMessageInterface $message) : void;
}
