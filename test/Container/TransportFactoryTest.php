<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger\Container;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Xtreamwayz\Expressive\Messenger\ConfigProvider;
use Xtreamwayz\Expressive\Messenger\Container\TransportFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class TransportFactoryTest extends TestCase
{
    /** @var array */
    private $config;

    public function setUp() : void
    {
        $this->config = (new ConfigProvider())();
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
            ['amqp://user:pass@example.com:5672/%2f/messages'],
            ['doctrine://doctrine.entity_manager.dbal_default'],
            ['doctrine://doctrine.entity_manager.orm_default'],
            ['in-memory:///'],
            ['sync://messenger.command.bus'],
            //['redis:'],
            //['redis://example.com:6379/messages'],
        ];
    }
}
