<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Container;

use Enqueue\Redis\RedisConnectionFactory;
use Interop\Queue\PsrContext;
use Psr\Container\ContainerInterface;

/**
 * @see https://github.com/php-enqueue/enqueue-dev/blob/master/docs/transport/redis.md#create-context
 *
 * Expected config
 *
 *  'redis' => [
 *      'host'   => '127.0.0.1',
 *      'port'   => 6379,
 *      'vendor' => 'phpredis',
 *  ],
 */
class RedisFactory
{
    public function __invoke(ContainerInterface $container) : PsrContext
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['redis'] ?? [];

        $factory = new RedisConnectionFactory($config);

        return $factory->createContext();
    }
}
