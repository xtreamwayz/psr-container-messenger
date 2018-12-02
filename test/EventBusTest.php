<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Xtreamwayz\Expressive\Messenger\ConfigProvider;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyEvent;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyEventHandler;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyQueryHandler;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class EventBusTest extends TestCase
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
        $container = $this->getContainer();

        /** @var MessageBus $eventBus */
        $eventBus = $container->get('messenger.bus.event');

        self::assertInstanceOf(MessageBusInterface::class, $eventBus);
        self::assertInstanceOf(MessageBus::class, $eventBus);
    }

    public function testItCanHaveNoHandlers() : void
    {
        $event = new DummyEvent();

        /** @var MessageBus $eventBus */
        $container = $this->getContainer();
        $eventBus  = $container->get('messenger.bus.event');
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
        $this->config['messenger']['buses']['messenger.bus.event']['handlers'][DummyEvent::class] = DummyEventHandler::class;
        // @codingStandardsIgnoreEnd

        /** @var MessageBus $eventBus */
        $container = $this->getContainer();
        $eventBus  = $container->get('messenger.bus.event');
        $eventBus->dispatch($event);
    }

    public function testItCanHaveMultipleHandlersForTheSameEvent() : void
    {
        $event = new DummyEvent();

        $eventHandler = $this->prophesize(DummyEventHandler::class);
        $eventHandler->__invoke($event)->shouldBeCalledTimes(2);

        $this->config['dependencies']['services'][DummyEventHandler::class] = $eventHandler->reveal();
        $this->config['dependencies']['services'][DummyQueryHandler::class] = $eventHandler->reveal();

        $this->config['messenger']['buses']['messenger.bus.event']['handlers'][DummyEvent::class] = [
            DummyEventHandler::class,
            DummyQueryHandler::class,
        ];

        /** @var MessageBus $eventBus */
        $container = $this->getContainer();
        $eventBus  = $container->get('messenger.bus.event');
        $eventBus->dispatch($event);
    }
}
