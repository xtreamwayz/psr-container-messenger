<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Transport;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection as TransportConnection;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;

use function sprintf;
use function strpos;

class DoctrineTransportFactory implements TransportFactoryInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): DoctrineTransport
    {
        /**
         * @var string[] $configuration
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        $configuration = TransportConnection::buildConfiguration($dsn, $options);

        try {
            /** @var Connection|EntityManager $driverConnection */
            $driverConnection = $this->container->get($configuration['connection']);
            if ($driverConnection instanceof EntityManager) {
                $driverConnection = $driverConnection->getConnection();
            }
        } catch (InvalidArgumentException $e) {
            throw new TransportException(
                sprintf('Could not find Doctrine connection from Messenger DSN "%s".', $dsn),
                0,
                $e
            );
        }

        /** @psalm-suppress InternalClass,InternalMethod */
        $transportConnection = new TransportConnection($configuration, $driverConnection);

        return new DoctrineTransport($transportConnection, $serializer);
    }

    public function supports(string $dsn, array $options): bool
    {
        return strpos($dsn, 'doctrine://') === 0;
    }
}
