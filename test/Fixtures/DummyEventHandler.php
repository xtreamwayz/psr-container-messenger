<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger\Fixtures;

class DummyEventHandler
{
    public function __invoke(DummyEvent $event) : void
    {
    }
}
