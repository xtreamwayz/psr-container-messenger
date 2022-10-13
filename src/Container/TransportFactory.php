<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Container;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransportFactory;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransportFactory;
use Symfony\Component\Messenger\Transport\InMemoryTransportFactory;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransportFactory;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Throwable;
use Xtreamwayz\PsrContainerMessenger\Transport\DoctrineTransportFactory;

use function explode;
use function sprintf;
use function trim;

class TransportFactory
{
    private string $dsnOrName;

    /**
     * Creates a new instance from a specified config
     *
     * <code>
     * <?php
     * return [
     *     // 'messenger.transport.<alias>' => [EnqueueTransportFactory::class, '<dns>'],
     *     'messenger.transport.redis' => [EnqueueTransportFactory::class, 'redis:'],
     * ];
     * </code>
     *
     * DSN must be a valid transport dsn:
     *
     *      redis:
     *      redis://example.com:6379/messages
     *      amqp://user:pass@example.com:5672/%2f/messages
     *      doctrine://doctrine.entity_manager.orm_default
     *      sync://messenger.command.bus
     *      in-memory:///
     *
     * @throws InvalidArgumentException
     */
    public static function __callStatic(string $dsn, array $arguments): SenderInterface
    {
        if (! isset($arguments[0]) || ! $arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(
                sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }

        return (new self($dsn))($arguments[0]);
    }

    public function __construct(?string $dsn = null)
    {
        $this->dsnOrName = $dsn ?? 'null:';
    }

    public function __invoke(ContainerInterface $container): TransportInterface
    {
        $config = $container->get('config')['messenger']['transports'] ?? [];
        $dsnOrName = $this->dsnOrName;
        $options = [];
        if (isset($config[$dsnOrName])) {
            $transportConfig = $config[$dsnOrName];
            $dsnOrName = $transportConfig['dsn'] ?? null;
            $options = $transportConfig['options'] ?? [];
        }
        $factory = $this->dsnToTransportFactory($container, $dsnOrName);

        return $factory->createTransport($dsnOrName, $options, $container->get('messenger.serializer'));
    }

    private function dsnToTransportFactory(ContainerInterface $container, string $dsn): TransportFactoryInterface
    {
        try {
            [$type, $config] = explode(':', $dsn, 2);
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException(
                sprintf('Invalid dsn string "%s".', $dsn),
                $throwable->getCode(),
                $throwable
            );
        }
        switch ($type) {
            case 'amqp':
                return new AmqpTransportFactory();

            case 'doctrine':
                return new DoctrineTransportFactory($container);

            case 'in-memory':
                return new InMemoryTransportFactory();

            case 'redis':
                return new RedisTransportFactory();

            case 'sync':
                return new SyncTransportFactory($container->get(trim($config, '/')));
        }

        throw new InvalidArgumentException(sprintf('No transport supports the given Messenger DSN "%s".', $dsn));
    }
}
