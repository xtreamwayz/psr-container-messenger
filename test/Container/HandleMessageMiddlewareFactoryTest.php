<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test\Container;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBus;
use Xtreamwayz\PsrContainerMessenger\ConfigProvider;
use Xtreamwayz\PsrContainerMessenger\Test\Fixtures\DummyCommand;
use Xtreamwayz\PsrContainerMessenger\Test\Fixtures\DummyCommandHandler;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

use function array_replace_recursive;

class HandleMessageMiddlewareFactoryTest extends TestCase
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

    public function testItLogs(): void
    {
        $command        = new DummyCommand();
        $commandHandler = $this->createMock(DummyCommandHandler::class);
        $commandHandler->expects($this->once())->method('__invoke')->with($command);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with(
            'Message {class} handled by {handler}',
            $this->isType('array')
        );

        // @codingStandardsIgnoreStart
        $this->config['dependencies']['services'][LoggerInterface::class]                             = $logger;
        $this->config['dependencies']['services'][DummyCommandHandler::class]                         = $commandHandler;
        $this->config['messenger']['logger']                                                          = LoggerInterface::class;
        $this->config['messenger']['buses']['messenger.command.bus']['handlers'][DummyCommand::class] = [DummyCommandHandler::class];
        // @codingStandardsIgnoreEnd

        /** @var MessageBus $commandBus */
        $container  = $this->getContainer();
        $commandBus = $container->get('messenger.command.bus');
        $commandBus->dispatch($command);
    }
}
