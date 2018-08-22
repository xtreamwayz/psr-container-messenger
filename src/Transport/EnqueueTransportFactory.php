<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Transport;

use Enqueue\AmqpBunny\AmqpConnectionFactory as AmqpBunnyConnectionFactory;
use Enqueue\AmqpExt\AmqpConnectionFactory as AmqpExtConnectionFactory;
use Enqueue\AmqpLib\AmqpConnectionFactory as AmqpLibConnectionFactory;
use Enqueue\Dbal\DbalConnectionFactory;
use Enqueue\Fs\FsConnectionFactory;
use Enqueue\Gearman\GearmanConnectionFactory;
use Enqueue\Gps\GpsConnectionFactory;
use Enqueue\Mongodb\MongodbConnectionFactory;
use Enqueue\Null\NullConnectionFactory;
use Enqueue\Pheanstalk\PheanstalkConnectionFactory;
use Enqueue\RdKafka\RdKafkaConnectionFactory;
use Enqueue\Redis\RedisConnectionFactory;
use Enqueue\Sqs\SqsConnectionFactory;
use Enqueue\Stomp\StompConnectionFactory;
use Interop\Queue\PsrConnectionFactory;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\TransportInterface;
use function array_key_exists;
use function array_keys;
use function class_exists;
use function explode;
use function implode;
use function sprintf;
use function strpos;

class EnqueueTransportFactory
{
    /** @var string */
    private $dsn;

    /** @var string */
    private $queueName;

    /**
     * EnqueueTransportFactory constructor
     *
     * DSN must be a valid Enqueue transport dsn:
     *
     *      redis:
     *      redis://example.com:1000?vendor=phpredis
     *      amqp://user:pass@example.com:10000/%2f
     *      kafka://example.com:1000
     *
     * @see https://github.com/php-enqueue/enqueue-dev/tree/master/docs/transport
     */
    public function __construct(string $dsn, ?string $queueName = null)
    {
        $this->dsn       = $dsn ?? 'null:';
        $this->queueName = $queueName ?? 'messenger.transport.default';
    }

    public function __invoke(ContainerInterface $container) : TransportInterface
    {
        // Version 0.8.35
        $psrContext = $this->dsnToConnectionFactory($this->dsn)->createContext();

        // Use with enqueue 0.9
        //$psrContext = (new ConnectionFactoryFactory())->create($this->dsn)->createContext();

        return new EnqueueTransport(
            $container->get(Serializer::class),
            $psrContext,
            $this->queueName
        );
    }

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

    private function dsnToConnectionFactory(string $dsn) : PsrConnectionFactory
    {
        $map = [];

        if (class_exists(FsConnectionFactory::class)) {
            $map['file'] = FsConnectionFactory::class;
        }

        if (class_exists(AmqpExtConnectionFactory::class)) {
            $map['amqp+ext']  = AmqpExtConnectionFactory::class;
            $map['amqps+ext'] = AmqpExtConnectionFactory::class;
        }
        if (class_exists(AmqpLibConnectionFactory::class)) {
            $map['amqp+lib']  = AmqpLibConnectionFactory::class;
            $map['amqps+lib'] = AmqpLibConnectionFactory::class;
        }
        if (class_exists(AmqpBunnyConnectionFactory::class)) {
            $map['amqp+bunny'] = AmqpBunnyConnectionFactory::class;
        }

        if (class_exists(AmqpExtConnectionFactory::class)) {
            $map['amqp'] = AmqpExtConnectionFactory::class;
        } elseif (class_exists(AmqpBunnyConnectionFactory::class)) {
            $map['amqp'] = AmqpBunnyConnectionFactory::class;
        } elseif (class_exists(AmqpLibConnectionFactory::class)) {
            $map['amqp'] = AmqpLibConnectionFactory::class;
        }

        if (class_exists(AmqpExtConnectionFactory::class)) {
            $map['amqps'] = AmqpExtConnectionFactory::class;
        } elseif (class_exists(AmqpLibConnectionFactory::class)) {
            $map['amqps'] = AmqpLibConnectionFactory::class;
        }

        if (class_exists(NullConnectionFactory::class)) {
            $map['null'] = NullConnectionFactory::class;
        }

        if (class_exists(DbalConnectionFactory::class)) {
            $map['db2']        = DbalConnectionFactory::class;
            $map['ibm_db2']    = DbalConnectionFactory::class;
            $map['mssql']      = DbalConnectionFactory::class;
            $map['pdo_sqlsrv'] = DbalConnectionFactory::class;
            $map['mysql']      = DbalConnectionFactory::class;
            $map['mysql2']     = DbalConnectionFactory::class;
            $map['pdo_mysql']  = DbalConnectionFactory::class;
            $map['pgsql']      = DbalConnectionFactory::class;
            $map['postgres']   = DbalConnectionFactory::class;
            $map['postgresql'] = DbalConnectionFactory::class;
            $map['pdo_pgsql']  = DbalConnectionFactory::class;
            $map['sqlite']     = DbalConnectionFactory::class;
            $map['sqlite3']    = DbalConnectionFactory::class;
            $map['pdo_sqlite'] = DbalConnectionFactory::class;
        }

        if (class_exists(GearmanConnectionFactory::class)) {
            $map['gearman'] = GearmanConnectionFactory::class;
        }

        if (class_exists(PheanstalkConnectionFactory::class)) {
            $map['beanstalk'] = PheanstalkConnectionFactory::class;
        }

        if (class_exists(RdKafkaConnectionFactory::class)) {
            $map['kafka']   = RdKafkaConnectionFactory::class;
            $map['rdkafka'] = RdKafkaConnectionFactory::class;
        }

        if (class_exists(RedisConnectionFactory::class)) {
            $map['redis'] = RedisConnectionFactory::class;
        }

        if (class_exists(StompConnectionFactory::class)) {
            $map['stomp'] = StompConnectionFactory::class;
        }

        if (class_exists(SqsConnectionFactory::class)) {
            $map['sqs'] = SqsConnectionFactory::class;
        }

        if (class_exists(GpsConnectionFactory::class)) {
            $map['gps'] = GpsConnectionFactory::class;
        }

        if (class_exists(MongodbConnectionFactory::class)) {
            $map['mongodb'] = MongodbConnectionFactory::class;
        }

        list($scheme) = explode(':', $dsn, 2);
        if ($scheme === false || strpos($dsn, ':') === false) {
            throw new \LogicException(sprintf('The scheme could not be parsed from DSN "%s"', $dsn));
        }

        if (array_key_exists($scheme, $map) === false) {
            throw new \LogicException(sprintf(
                'The scheme "%s" is not supported. Supported "%s"',
                $scheme,
                implode('", "', array_keys($map))
            ));
        }

        $factoryClass = $map[$scheme];

        return new $factoryClass($dsn);
    }
}
