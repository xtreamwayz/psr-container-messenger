<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Xtreamwayz\Expressive\Messenger\ConfigProvider;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyQuery;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyQueryHandler;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use function sprintf;

class QueryBusTest extends TestCase
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

        /** @var MessageBus $queryBus */
        $queryBus = $container->get('messenger.bus.query');

        self::assertInstanceOf(MessageBusInterface::class, $queryBus);
        self::assertInstanceOf(MessageBus::class, $queryBus);
    }

    public function testItMustHaveOneQueryHandler() : void
    {
        $query = new DummyQuery();

        $this->expectException(NoHandlerForMessageException::class);
        $this->expectExceptionMessage(sprintf('No handler for message "%s"', DummyQuery::class));

        /** @var MessageBus $queryBus */
        $container = $this->getContainer();
        $queryBus  = $container->get('messenger.bus.query');
        $queryBus->dispatch($query);
    }

    public function testItCanHandleQueries() : void
    {
        $query = new DummyQuery();

        $queryHandler = $this->prophesize(DummyQueryHandler::class);
        $queryHandler->__invoke($query)->shouldBeCalled();

        // @codingStandardsIgnoreStart
        $this->config['dependencies']['services'][DummyQueryHandler::class]                       = $queryHandler->reveal();
        $this->config['messenger']['buses']['messenger.bus.query']['handlers'][DummyQuery::class] = DummyQueryHandler::class;
        // @codingStandardsIgnoreEnd

        /** @var MessageBus $queryBus */
        $container = $this->getContainer();
        $queryBus  = $container->get('messenger.bus.query');
        $queryBus->dispatch($query);
    }

    public function testItReturnsTheQueryResult() : void
    {
        $query = new DummyQuery();
        $data  = ['foo' => 'bar'];

        $queryHandler = $this->prophesize(DummyQueryHandler::class);
        $queryHandler->__invoke($query)->shouldBeCalled()->willReturn($data);

        // @codingStandardsIgnoreStart
        $this->config['dependencies']['services'][DummyQueryHandler::class]                       = $queryHandler->reveal();
        $this->config['messenger']['buses']['messenger.bus.query']['handlers'][DummyQuery::class] = DummyQueryHandler::class;
        // @codingStandardsIgnoreEnd

        /** @var MessageBus $queryBus */
        $container = $this->getContainer();
        $queryBus  = $container->get('messenger.bus.query');
        $result    = $queryBus->dispatch($query);

        self::assertEquals($data, $result);
    }
}
