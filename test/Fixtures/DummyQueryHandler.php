<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test\Fixtures;

class DummyQueryHandler
{
    public function __invoke(DummyQuery $query): array
    {
        return [];
    }
}
