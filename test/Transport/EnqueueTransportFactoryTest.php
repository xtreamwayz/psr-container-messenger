<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger\Transport;

use Enqueue\Null\NullContext;
use Enqueue\Redis\RedisContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Xtreamwayz\Expressive\Messenger\ConfigProvider;
use Xtreamwayz\Expressive\Messenger\Transport\EnqueueTransport;
use Xtreamwayz\Expressive\Messenger\Transport\EnqueueTransportFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class EnqueueTransportFactoryTest extends TestCase
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

    public function testItCanBeConstructed() : void
    {
        $this->config['dependencies']['factories']['transport.null'] = [EnqueueTransportFactory::class, 'null:'];

        /** @var EnqueueTransport $transport */
        $container = $this->getContainer();
        $transport = $container->get('transport.null');

        self::assertInstanceOf(EnqueueTransport::class, $transport);
        self::assertInstanceOf(TransportInterface::class, $transport);
        self::assertInstanceOf(ReceiverInterface::class, $transport);
        self::assertInstanceOf(SenderInterface::class, $transport);
        self::assertAttributeEquals('transport.null', 'queueName', $transport);
    }

    public function testItUsesCustomDsn() : void
    {
        $this->config['dependencies']['factories']['foo.bar'] = [EnqueueTransportFactory::class, 'null:'];

        /** @var EnqueueTransport $transport */
        $container  = $this->getContainer();
        $transport  = $container->get('foo.bar');
        $psrContext = self::readAttribute($transport, 'psrContext');

        self::assertInstanceOf(NullContext::class, $psrContext);
        self::assertAttributeEquals('foo.bar', 'queueName', $transport);
    }

    public function testItCreatesPsrontextFromDns() : void
    {
        $this->config['dependencies']['factories']['redis.transport'] = [EnqueueTransportFactory::class, 'redis:'];

        /** @var EnqueueTransport $transport */
        $container  = $this->getContainer();
        $transport  = $container->get('redis.transport');
        $psrContext = self::readAttribute($transport, 'psrContext');

        self::assertInstanceOf(RedisContext::class, $psrContext);
        self::assertAttributeEquals('redis.transport', 'queueName', $transport);
    }
}
