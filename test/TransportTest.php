<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Xtreamwayz\Expressive\Messenger\ConfigProvider;
use Xtreamwayz\Expressive\Messenger\Container\TransportFactory;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyMessage;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class TransportTest extends TestCase
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

    public function testItCanSendAndReceiveMessages() : void
    {
        $this->config['dependencies']['factories']['in-memory-transport'] = [TransportFactory::class, 'in-memory:///'];

        /** @var TransportInterface $transport */
        $transport = $this->getContainer()->get('in-memory-transport');

        $message  = new DummyMessage('Hello');
        $envelope = new Envelope($message);
        $result   = $transport->send($envelope);

        self::assertEquals($result, $envelope);
        self::assertSame([$envelope], $transport->get());
    }
}
