<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger;

use Interop\Queue\PsrContext;
use Symfony\Component\Messenger\Asynchronous\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

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
                'messenger.transport.default' => [Queue\QueueTransportFactory::class, 'messenger.transport.default'],

                Command\MessengerConsumerCommand::class => Command\MessengerConsumerCommandFactory::class,
                HandleMessageMiddleware::class          => Container\HandleMessageMiddlewareFactory::class,
                MessageBusInterface::class              => Container\MessageBusFactory::class,
                PsrContext::class                       => Container\RedisFactory::class,
                SendMessageMiddleware::class            => Container\SendMessageMiddlewareFactory::class,
                SerializerInterface::class              => Container\SerializerFactory::class,
                Serializer::class                       => Container\TransportSerializerFactory::class,
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function getMessenger() : array
    {
        return [
            'middleware' => [
                SendMessageMiddleware::class,
                HandleMessageMiddleware::class,
            ],

            // These are loaded into the SendMessageMiddleware
            // App\MyMessage::class => 'messenger.transport.default',
            'routing'    => [],
        ];
    }

    public function getConsole() : array
    {
        return [
            'commands' => [
                'messenger:consume' => Command\MessengerConsumerCommand::class
            ],
        ];
    }
}
