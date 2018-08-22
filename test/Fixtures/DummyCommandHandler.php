<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger\Fixtures;

class DummyCommandHandler
{
    public function __invoke(DummyCommand $command) : void
    {
    }
}
