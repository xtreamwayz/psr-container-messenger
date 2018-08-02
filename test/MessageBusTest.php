<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger;

use Enqueue\Redis\Redis;
use Enqueue\Redis\RedisContext;
use Interop\Queue\PsrContext;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Asynchronous\Transport\ReceivedMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Xtreamwayz\Expressive\Messenger\ConfigProvider;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyMessage;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyMessageHandlerInterface;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use function json_encode;
use function sprintf;

class MessageBusTest extends TestCase
{
    /** @var array */
    private $config;

    /** @var array */
    private $dependencies;

    /** @var ContainerInterface */
    private $container;

    protected function setUp() : void
    {
        $this->config       = (new ConfigProvider())();
        $this->dependencies = $this->config['dependencies'];
    }

    private function createMessageBus() : MessageBusInterface
    {
        $this->container = new ServiceManager();
        (new Config($this->dependencies))->configureServiceManager($this->container);
        $this->container->setService('config', $this->config);

        return $this->container->get(MessageBusInterface::class);
    }

    public function testItHasNoDummyHandler() : void
    {
        $bus = $this->createMessageBus();

        $message  = new DummyMessage('Hello');
        $envelope = new Envelope($message, [new ReceivedMessage()]);

        $this->expectException(NoHandlerForMessageException::class);
        $this->expectExceptionMessage(sprintf('No handler for message "%s".', DummyMessage::class));

        $bus->dispatch($envelope);
    }

    public function testItCanHandleMessages() : void
    {
        $message  = new DummyMessage('Hello');
        $envelope = new Envelope($message, [new ReceivedMessage()]);

        $handler = $this->prophesize(DummyMessageHandlerInterface::class);
        $handler->__invoke($message)->shouldBeCalled();

        $this->dependencies['services']['handler.' . DummyMessage::class] = $handler->reveal();

        $bus = $this->createMessageBus();
        $bus->dispatch($envelope);
    }

    public function testItCanSendMessagesToTheQueue() : void
    {
        $this->config['messenger']['routing'][DummyMessage::class] = 'messenger.transport.default';

        $message  = new DummyMessage('Hello');
        $envelope = new Envelope($message);

        $redis = $this->prophesize(Redis::class);
        $redis->lpush('messenger.transport.default', Argument::type('string'))->shouldBeCalled();
        $psrContext = new RedisContext($redis->reveal());

        $this->dependencies['services'][PsrContext::class] = $psrContext;

        $bus = $this->createMessageBus();
        $bus->dispatch($envelope);
    }

    public function testItReceivesMessagesFromTheQueue() : void
    {
        $this->config['messenger']['routing'][DummyMessage::class] = 'messenger.transport.default';

        $redis      = $this->prophesize(Redis::class);
        $psrContext = new RedisContext($redis->reveal());

        $this->dependencies['services'][PsrContext::class] = $psrContext;
        $this->createMessageBus();

        $message        = new DummyMessage('Hello');
        $envelope       = new Envelope($message);
        $serializer     = $this->container->get(Serializer::class);
        $encodedMessage = $serializer->encode($envelope);
        $psrMessage     = $psrContext->createMessage(
            $encodedMessage['body'],
            $encodedMessage['properties'] ?? [],
            $encodedMessage['headers'] ?? []
        );

        $redis
            ->brpop('messenger.transport.default', 1)
            ->willReturn(json_encode($psrMessage))
            ->shouldBeCalledTimes(1);

        $transport = $this->container->get('messenger.transport.default');
        $transport->receive(function (?Envelope $envelope) use ($transport) : void {
            $transport->stop();

            self::assertNotNull($envelope);

            $message = $envelope->getMessage();
            self::assertInstanceOf(DummyMessage::class, $message);
            self::assertEquals('hello', $message->getMessage());
        });
    }
}
