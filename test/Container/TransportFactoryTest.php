<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger\Container;

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
        $this->config['dependencies']['factories']['messenger.transport.test'] = [TransportFactory::class, $dns];

        /** @var TransportInterface $transport */
        $transport = $this->getContainer()->get('messenger.transport.test');

        self::assertInstanceOf(TransportInterface::class, $transport);
        self::assertInstanceOf(ReceiverInterface::class, $transport);
        self::assertInstanceOf(SenderInterface::class, $transport);
    }

    public function dnsProvider() : array
    {
        return [
            ['amqp://user:pass@example.com:5672/%2f/messages'],
            //['doctrine://orm_default'],
            ['in-memory:///'],
            ['sync://messenger.command.bus'],
            //['redis:'],
            //['redis://example.com:6379/messages'],
        ];
    }
}
