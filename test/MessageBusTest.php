<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Asynchronous\Transport\ReceivedMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;
use Xtreamwayz\Expressive\Messenger\ConfigProvider;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyCommand;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyCommandHandlerFactory;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyMessage;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use function sprintf;

class MessageBusTest extends TestCase
{
    /** @var array */
    private $config;

    /** @var array */
    private $dependencies;

    protected function setUp() : void
    {
        $this->config       = (new ConfigProvider())();
        $this->dependencies = $this->config['dependencies'];
    }

    private function createMessageBus() : MessageBusInterface
    {
        $container = new ServiceManager();
        (new Config($this->dependencies))->configureServiceManager($container);
        $container->setService('config', $this->config);

        return $container->get(MessageBusInterface::class);
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
        $this->dependencies['factories']['handler.' . DummyCommand::class] = DummyCommandHandlerFactory::class;

        $message  = new DummyCommand('Hello');
        $envelope = new Envelope($message, [new ReceivedMessage()]);

        $bus = $this->createMessageBus();
        $bus->dispatch($envelope);
    }

    public function testItCanQueueMessages() : void
    {
        $this->config['messenger']['routing'][DummyCommand::class] = 'messenger.transport.default';

        $message  = new DummyCommand('Hello');
        $envelope = new Envelope($message);

        $bus = $this->createMessageBus();

        $this->expectExceptionMessage('No connection could be made because the target machine actively refused it.');

        $bus->dispatch($envelope);
    }
}
