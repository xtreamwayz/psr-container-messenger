<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Xtreamwayz\PsrContainerMessenger\ConfigProvider;
use Xtreamwayz\PsrContainerMessenger\Test\Fixtures\DummyQuery;
use Xtreamwayz\PsrContainerMessenger\Test\Fixtures\DummyQueryHandler;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

use function array_replace_recursive;
use function sprintf;

class QueryBusTest extends TestCase
{
    private array $config;

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

        $queryBus = $container->get('messenger.query.bus');

        $this->assertInstanceOf(MessageBusInterface::class, $queryBus);
        $this->assertInstanceOf(MessageBus::class, $queryBus);
    }

    public function testItMustHaveOneQueryHandler(): void
    {
        $query = new DummyQuery();

        $this->expectException(NoHandlerForMessageException::class);
        $this->expectExceptionMessage(sprintf('No handler for message "%s"', DummyQuery::class));

        $container = $this->getContainer();
        $queryBus  = $container->get('messenger.query.bus');
        $queryBus->dispatch($query);
    }

    public function testItCanHandleQueries(): void
    {
        $query = new DummyQuery();

        $queryHandler = $this->createMock(DummyQueryHandler::class);
        $queryHandler->expects($this->once())->method('__invoke')->with($query);

        // @codingStandardsIgnoreStart
        $this->config['dependencies']['services'][DummyQueryHandler::class]                       = $queryHandler;
        $this->config['messenger']['buses']['messenger.query.bus']['handlers'][DummyQuery::class] = [DummyQueryHandler::class];
        // @codingStandardsIgnoreEnd

        $container = $this->getContainer();
        $queryBus  = $container->get('messenger.query.bus');
        $queryBus->dispatch($query);
    }

    public function testItReturnsTheQueryResult(): void
    {
        $query = new DummyQuery();
        $data  = ['foo' => 'bar'];

        $queryHandler = $this->createMock(DummyQueryHandler::class);
        $queryHandler->expects($this->once())->method('__invoke')->with($query)->willReturn($data);

        // @codingStandardsIgnoreStart
        $this->config['dependencies']['services'][DummyQueryHandler::class]                       = $queryHandler;
        $this->config['messenger']['buses']['messenger.query.bus']['handlers'][DummyQuery::class] = [DummyQueryHandler::class];
        // @codingStandardsIgnoreEnd

        $container = $this->getContainer();
        $queryBus  = $container->get('messenger.query.bus');
        $result    = $queryBus->dispatch($query);

        $lastStamp = $result->last(HandledStamp::class);
        $this->assertNotNull($lastStamp);
        $this->assertEquals($data, $lastStamp->getResult());
    }
}
