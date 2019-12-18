<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger\Fixtures;

class DummyEventHandlerTwo
{
    public function __invoke(DummyEvent $event) : void
    {
    }
}
