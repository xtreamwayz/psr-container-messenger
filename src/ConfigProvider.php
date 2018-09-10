<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger;

use Symfony\Component\Messenger\Asynchronous\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\AllowNoHandlerMiddleware;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\LoggingMiddleware;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Xtreamwayz\Expressive\Messenger\Container\LoggingMiddlewareFactory;
use Xtreamwayz\Expressive\Messenger\Container\MessageBusFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

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
                AllowNoHandlerMiddleware::class   => InvokableFactory::class,
                HandleMessageMiddleware::class    => Container\HandleMessageMiddlewareFactory::class,
                LoggingMiddleware::class          => LoggingMiddlewareFactory::class,
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
                    'handlers'   => [],
                    'middleware' => [],
                    'routes'     => [],
                ],
                'messenger.bus.event'   => [
                    'handlers'   => [],
                    'middleware' => [
                        AllowNoHandlerMiddleware::class,
                    ],
                    'routes'     => [],
                ],
                'messenger.bus.query'   => [
                    'handlers'   => [],
                    'middleware' => [],
                    'routes'     => [],
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
