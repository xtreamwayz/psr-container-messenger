<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger\Transport;

use Enqueue\Redis\Redis;
use Enqueue\Redis\RedisContext;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Xtreamwayz\Expressive\Messenger\ConfigProvider;
use Xtreamwayz\Expressive\Messenger\Transport\EnqueueTransport;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyMessage;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use function json_encode;

class EnqueueTransportTest extends TestCase
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

    public function testItCanSendMessages() : void
    {
        $redis = $this->prophesize(Redis::class);
        $redis->lpush('messenger.transport.redis', Argument::type('string'))->shouldBeCalled();

        $container = $this->getContainer();
        $transport = new EnqueueTransport(
            $container->get(Serializer::class),
            new RedisContext($redis->reveal()),
            'messenger.transport.redis'
        );

        $message  = new DummyMessage('Hello');
        $envelope = new Envelope($message);

        $transport->send($envelope);
    }

    public function testItCanReceiveMessages() : void
    {
        $redis = $this->prophesize(Redis::class);

        $container  = $this->getContainer();
        $serializer = $container->get(Serializer::class);
        $psrContext = new RedisContext($redis->reveal());
        $transport  = new EnqueueTransport($serializer, $psrContext, 'messenger.transport.redis');

        $message        = new DummyMessage('Hello');
        $envelope       = new Envelope($message);
        $encodedMessage = $serializer->encode($envelope);
        $psrMessage     = $psrContext->createMessage(
            $encodedMessage['body'],
            $encodedMessage['properties'] ?? [],
            $encodedMessage['headers'] ?? []
        );

        $redis
            ->brpop('messenger.transport.redis', 1)
            ->willReturn(json_encode($psrMessage))
            ->shouldBeCalledTimes(1);

        $transport->receive(static function (Envelope $envelope) use ($transport) : void {
            $transport->stop();
            self::assertNotNull($envelope);
            $message = $envelope->getMessage();
            self::assertInstanceOf(DummyMessage::class, $message);
            self::assertEquals('hello', $message->getMessage());
        });
    }
}
