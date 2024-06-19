<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test\Container;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Xtreamwayz\PsrContainerMessenger\ConfigProvider;
use Xtreamwayz\PsrContainerMessenger\Container\TransportFactory;

use function array_replace_recursive;

class TransportFactoryTest extends TestCase
{
    private array $config;

    public function setUp(): void
    {
        $this->config = array_replace_recursive((new ConfigProvider())(), require 'example/basic-config.php');
    }

    private function getContainer(): ServiceManager
    {
        $container = new ServiceManager();
        (new Config($this->config['dependencies']))->configureServiceManager($container);
        $container->setService('config', $this->config);

        return $container;
    }

    /**
     * @dataProvider dnsProvider
     */
    public function testDns(string $dns): void
    {
        $this->config['dependencies']['factories']['transport.test'] = [TransportFactory::class, $dns];

        $dbal = $this->createMock(DBALConnection::class);
        $orm  = $this->createMock(EntityManager::class);
        $orm->method('getConnection')->willReturn($dbal);

        $this->config['dependencies']['services']['doctrine.entity_manager.dbal_default'] = $dbal;
        $this->config['dependencies']['services']['doctrine.entity_manager.orm_default']  = $orm;

        $transport = $this->getContainer()->get('transport.test');

        $this->assertInstanceOf(ReceiverInterface::class, $transport);
        $this->assertInstanceOf(SenderInterface::class, $transport);
    }

    /**
     * @dataProvider configProvider
     */
    public function testConfig(string $alias, array $transportConfig): void
    {
        $this->config['dependencies']['factories']['transport.test'] = [TransportFactory::class, $alias];

        $dbal = $this->createMock(DBALConnection::class);
        $orm  = $this->createMock(EntityManager::class);
        $orm->method('getConnection')->willReturn($dbal);

        $this->config['dependencies']['services']['doctrine.entity_manager.dbal_default'] = $dbal;
        $this->config['dependencies']['services']['doctrine.entity_manager.orm_default']  = $orm;
        $this->config['messenger']['transports'][$alias] = $transportConfig;

        $transport = $this->getContainer()->get('transport.test');

        $this->assertInstanceOf(ReceiverInterface::class, $transport);
        $this->assertInstanceOf(SenderInterface::class, $transport);
    }

    public function testInvalidDsn()
    {
        $this->config['dependencies']['factories']['transport.test'] = [TransportFactory::class, 'invalid-dsn'];

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessageMatches('#.+Invalid dsn string ".+?".+#');

        $this->getContainer()->get('transport.test');
    }

    public function testInvalidTransport()
    {
        $this->config['dependencies']['factories']['transport.test'] = [TransportFactory::class, 'invalid://transport'];

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessageMatches('#.+No transport supports the given Messenger DSN ".*?".+#');

        $this->getContainer()->get('transport.test');
    }

    public function dnsProvider(): array
    {
        return [
            ['doctrine://doctrine.entity_manager.dbal_default'],
            ['doctrine://doctrine.entity_manager.orm_default'],
            ['in-memory:///'],
            ['sync://messenger.command.bus'],
            //['redis:'],
            //['redis://example.com:6379/messages'],
        ];
    }

    public function configProvider(): array
    {
        return [
            ['doctrine-dbal', ['dsn' => 'doctrine://doctrine.entity_manager.dbal_default']],
            ['doctrine-orm', ['dsn' => 'doctrine://doctrine.entity_manager.orm_default']],
            ['memory', ['dsn' => 'in-memory:///']],
            ['sync', ['dsn' => 'sync://messenger.command.bus']],
        ];
    }
}
