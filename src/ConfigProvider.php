<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\LoggingMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Xtreamwayz\Expressive\Messenger\Container\MessageBusFactory;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'messenger'    => $this->getMessenger(),
            'console'      => $this->getConsole(),
        ];
    }

    public function getDependencies() : array
    {
        // @codingStandardsIgnoreStart
        return [
            'factories' => [
                MessageBusInterface::class        => Container\MessageBusFactory::class,
                'messenger.bus.command'           => [MessageBusFactory::class, 'messenger.bus.command'],
                'messenger.bus.event'             => [MessageBusFactory::class, 'messenger.bus.event'],
                'messenger.bus.query'             => [MessageBusFactory::class, 'messenger.bus.query'],

                // Command
                Command\CommandQueueWorker::class => Command\CommandQueueWorkerFactory::class,

                // Middleware
                HandleMessageMiddleware::class    => Container\HandleMessageMiddlewareFactory::class,
                LoggingMiddleware::class          => Container\LoggingMiddlewareFactory::class,
                SendMessageMiddleware::class      => Container\SendMessageMiddlewareFactory::class,

                // Transport
                SerializerInterface::class        => Container\SerializerFactory::class,
                Serializer::class                 => Container\TransportSerializerFactory::class,
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function getMessenger() : array
    {
        return [
            'default_bus'        => 'messenger.bus.command',
            'default_middleware' => true,
            'buses'              => [
                'messenger.bus.command' => [
                    'allows_no_handler' => false,
                    'handlers'          => [],
                    'middleware'        => [],
                    'routes'            => [],
                    'send_and_handle'   => [],
                ],
                'messenger.bus.event'   => [
                    'allows_no_handler' => true,
                    'handlers'          => [],
                    'middleware'        => [],
                    'routes'            => [],
                    'send_and_handle'   => [],
                ],
                'messenger.bus.query'   => [
                    'allows_no_handler' => false,
                    'handlers'          => [],
                    'middleware'        => [],
                    'routes'            => [],
                    'send_and_handle'   => [],
                ],
            ],
        ];
    }

    public function getConsole() : array
    {
        return [
            'commands' => [
                'messenger:consume' => Command\CommandQueueWorker::class,
            ],
        ];
    }
}
