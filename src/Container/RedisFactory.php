<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Container;

use Enqueue\Redis\RedisConnectionFactory;
use Interop\Queue\PsrContext;
use Psr\Container\ContainerInterface;

class RedisFactory
{
    public function __invoke(ContainerInterface $container) : PsrContext
    {
        $factory = new RedisConnectionFactory(['host' => 'redis']);

        return $factory->createContext();
    }
}
