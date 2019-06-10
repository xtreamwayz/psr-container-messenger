<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpTransportFactory;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransportFactory;
use Symfony\Component\Messenger\Transport\InMemoryTransportFactory;
use Symfony\Component\Messenger\Transport\RedisExt\RedisTransportFactory;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\Sync\SyncTransportFactory;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use function explode;
use function sprintf;
use function trim;

class TransportFactory
{
    /** @var string */
    private $dsn;

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
     * @throws InvalidArgumentException
     */
    public static function __callStatic(string $dsn, array $arguments) : SenderInterface
    {
        if (! isset($arguments[0]) || ! $arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(
                sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }

        return (new self($dsn, $arguments[1] ?? 'messenger.transport.default'))($arguments[0]);
    }

    /**
     * TransportFactory constructor
     *
     * DSN must be a valid transport dsn:
     *
     *      redis:
     *      redis://example.com:6379/messages
     *      amqp://user:pass@example.com:5672/%2f/messages
     *      doctrine://orm_default
     *      sync://messenger.command.bus
     *      in-memory:///
     *
     * @see https://github.com/php-enqueue/enqueue-dev/tree/master/docs/transport
     */
    public function __construct(string $dsn, ?string $queueName = null)
    {
        $this->dsn = $dsn ?? 'null:';
    }

    public function __invoke(ContainerInterface $container) : TransportInterface
    {
        $factory = $this->dsnToTransportFactory($container, $this->dsn);

        return $factory->createTransport($this->dsn, [], new Serializer());
    }

    private function dsnToTransportFactory(ContainerInterface $container, string $dsn) : TransportFactoryInterface
    {
        [$type, $config] = explode(':', $dsn, 2);
        switch ($type) {
            case 'amqp':
                return new AmqpTransportFactory();
            case 'doctrine':
                return new DoctrineTransportFactory(
                    $container->get(sprintf('doctrine.entity_manager.%s', $config))
                );
            case 'redis':
                return new RedisTransportFactory();
            case 'sync':
                return new SyncTransportFactory($container->get(trim('/', $config)));
            case 'in-memory':
                return new InMemoryTransportFactory();
        }

        throw new InvalidArgumentException(sprintf('No transport supports the given Messenger DSN "%s".', $dsn));
    }
}
