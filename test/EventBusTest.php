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

    public function testItCanBeConstructed() : void
    {
        $container = $this->getContainer();

        /** @var MessageBus $eventBus */
        $eventBus = $container->get('messenger.event.bus');

        self::assertInstanceOf(MessageBusInterface::class, $eventBus);
        self::assertInstanceOf(MessageBus::class, $eventBus);
    }

    public function testItCanHaveNoHandlers() : void
    {
        $event = new DummyEvent();

        /** @var MessageBus $eventBus */
        $container = $this->getContainer();
        $eventBus  = $container->get('messenger.event.bus');
        $result    = $eventBus->dispatch($event);

        self::assertSame($event, $result->getMessage());
        self::assertEmpty($result->all());
    }

    public function testItCanHandleEvents() : void
    {
        $event = new DummyEvent();

        $eventHandler = $this->prophesize(DummyEventHandler::class);
        $eventHandler->__invoke($event)->shouldBeCalled();

        // @codingStandardsIgnoreStart
        $this->config['dependencies']['services'][DummyEventHandler::class]                       = $eventHandler->reveal();
        $this->config['messenger']['buses']['messenger.event.bus']['handlers'][DummyEvent::class] = [DummyEventHandler::class];
        // @codingStandardsIgnoreEnd

        /** @var MessageBus $eventBus */
        $container = $this->getContainer();
        $eventBus  = $container->get('messenger.event.bus');
        $eventBus->dispatch($event);
    }

    public function testItCanHaveMultipleHandlersForTheSameEvent() : void
    {
        $event = new DummyEvent();

        $eventHandler1 = $this->prophesize(DummyEventHandler::class);
        $eventHandler1->__invoke($event)->shouldBeCalledTimes(1);
        $eventHandler2 = $this->prophesize(DummyEventHandlerTwo::class);
        $eventHandler2->__invoke($event)->shouldBeCalledTimes(1);

        $this->config['dependencies']['services'][DummyEventHandler::class]    = $eventHandler1->reveal();
        $this->config['dependencies']['services'][DummyEventHandlerTwo::class] = $eventHandler2->reveal();

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
