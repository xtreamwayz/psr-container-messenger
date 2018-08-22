<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger\Fixtures;

class DummyQueryHandler
{
    public function __invoke(DummyQuery $query) : array
    {
    }
}
