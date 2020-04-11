<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test\Container;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Xtreamwayz\PsrContainerMessenger\ConfigProvider;
use Xtreamwayz\PsrContainerMessenger\Container\TransportFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use function array_replace_recursive;

class TransportFactoryTest extends TestCase
{
    /** @var array */
    private $config;

    public function setUp() : void
    {
        $this->config = array_replace_recursive((new ConfigProvider())(), require 'example/basic-config.php');
    }

    private function getContainer() : ServiceManager
    {
        $container = new ServiceManager();
        (new Config($this->config['dependencies']))->configureServiceManager($container);
        $container->setService('config', $this->config);

        return $container;
    }

    /**
     * @dataProvider dnsProvider
     */
    public function testDns(string $dns) : void
    {
        $this->config['dependencies']['factories']['transport.test'] = [TransportFactory::class, $dns];

        $dbal = $this->prophesize(DBALConnection::class);
        $orm  = $this->prophesize(EntityManager::class);
        $orm->getConnection()->willReturn($dbal->reveal());

        $this->config['dependencies']['services']['doctrine.entity_manager.dbal_default'] = $dbal->reveal();
        $this->config['dependencies']['services']['doctrine.entity_manager.orm_default']  = $orm->reveal();

        /** @var TransportInterface $transport */
        $transport = $this->getContainer()->get('transport.test');

        self::assertInstanceOf(TransportInterface::class, $transport);
        self::assertInstanceOf(ReceiverInterface::class, $transport);
        self::assertInstanceOf(SenderInterface::class, $transport);
    }

    public function dnsProvider() : array
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
}
