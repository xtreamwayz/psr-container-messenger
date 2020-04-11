<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Xtreamwayz\PsrContainerMessenger\ConfigProvider;
use Xtreamwayz\PsrContainerMessenger\Test\Fixtures\DummyCommand;
use Xtreamwayz\PsrContainerMessenger\Test\Fixtures\DummyCommandHandler;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

use function array_replace_recursive;
use function sprintf;

class CommandBusTest extends TestCase
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

        $commandBus = $container->get('messenger.command.bus');

        $this->assertInstanceOf(MessageBusInterface::class, $commandBus);
        $this->assertInstanceOf(MessageBus::class, $commandBus);
    }

    public function testItMustHaveOneCommandHandler(): void
    {
        $command = new DummyCommand();

        $this->expectException(NoHandlerForMessageException::class);
        $this->expectExceptionMessage(sprintf('No handler for message "%s"', DummyCommand::class));

        $container  = $this->getContainer();
        $commandBus = $container->get('messenger.command.bus');
        $commandBus->dispatch($command);
    }

    public function testItCanHandleCommands(): void
    {
        $command        = new DummyCommand();
        $commandHandler = $this->createMock(DummyCommandHandler::class);
        $commandHandler->expects($this->once())->method('__invoke')->with($command);

        // @codingStandardsIgnoreStart
        $this->config['dependencies']['services'][DummyCommandHandler::class]                         = $commandHandler;
        $this->config['messenger']['buses']['messenger.command.bus']['handlers'][DummyCommand::class] = [DummyCommandHandler::class];
        // @codingStandardsIgnoreEnd

        $container  = $this->getContainer();
        $commandBus = $container->get('messenger.command.bus');
        $commandBus->dispatch($command);
    }
}
