<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Xtreamwayz\PsrContainerMessenger\ConfigProvider;
use Xtreamwayz\PsrContainerMessenger\Test\Fixtures\DummyEvent;
use Xtreamwayz\PsrContainerMessenger\Test\Fixtures\DummyEventHandler;
use Xtreamwayz\PsrContainerMessenger\Test\Fixtures\DummyEventHandlerTwo;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

use function array_replace_recursive;

class EventBusTest extends TestCase
{
    /** @var array */
    private $config;

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

    public function testItCanBeConstructed(): void
    {
        $container = $this->getContainer();

        /** @var MessageBus $eventBus */
        $eventBus = $container->get('messenger.event.bus');

        $this->assertInstanceOf(MessageBusInterface::class, $eventBus);
        $this->assertInstanceOf(MessageBus::class, $eventBus);
    }

    public function testItCanHaveNoHandlers(): void
    {
        $event = new DummyEvent();

        /** @var MessageBus $eventBus */
        $container = $this->getContainer();
        $eventBus  = $container->get('messenger.event.bus');
        $result    = $eventBus->dispatch($event);

        $this->assertSame($event, $result->getMessage());
        $this->assertEmpty($result->all());
    }

    public function testItCanHandleEvents(): void
    {
        $event = new DummyEvent();

        $eventHandler = $this->createMock(DummyEventHandler::class);
        $eventHandler->expects($this->once())->method('__invoke')->with($event);

        // @codingStandardsIgnoreStart
        $this->config['dependencies']['services'][DummyEventHandler::class]                       = $eventHandler;
        $this->config['messenger']['buses']['messenger.event.bus']['handlers'][DummyEvent::class] = [DummyEventHandler::class];
        // @codingStandardsIgnoreEnd

        /** @var MessageBus $eventBus */
        $container = $this->getContainer();
        $eventBus  = $container->get('messenger.event.bus');
        $eventBus->dispatch($event);
    }

    public function testItCanHaveMultipleHandlersForTheSameEvent(): void
    {
        $event = new DummyEvent();

        $eventHandler1 = $this->createMock(DummyEventHandler::class);
        $eventHandler1->expects($this->once())->method('__invoke')->with($event);
        $eventHandler2 = $this->createMock(DummyEventHandlerTwo::class);
        $eventHandler2->expects($this->once())->method('__invoke')->with($event);

        $this->config['dependencies']['services'][DummyEventHandler::class]    = $eventHandler1;
        $this->config['dependencies']['services'][DummyEventHandlerTwo::class] = $eventHandler2;

        $this->config['messenger']['buses']['messenger.event.bus']['handlers'][DummyEvent::class] = [
            DummyEventHandler::class,
            DummyEventHandlerTwo::class,
        ];

        /** @var MessageBus $eventBus */
        $container = $this->getContainer();
        $eventBus  = $container->get('messenger.event.bus');
        $eventBus->dispatch($event);
    }
}
