<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger;

use Interop\Queue\PsrContext;
use Symfony\Component\Messenger\Asynchronous\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'messenger'    => $this->getMessenger(),
        ];
    }

    public function getDependencies() : array
    {
        // @codingStandardsIgnoreStart
        return [
            'factories' => [
                'queue.default.receiver' => [Queue\QueueReceiverFactory::class, 'queue.default'],
                'queue.default.sender'   => [Queue\QueueSenderFactory::class, 'queue.default'],

                Command\MessengerConsumerCommand::class => Command\MessengerConsumerCommandFactory::class,
                DecoderInterface::class                 => Container\DecoderFactory::class,
                EncoderInterface::class                 => Container\EncoderFactory::class,
                HandleMessageMiddleware::class          => Container\HandleMessageMiddlewareFactory::class,
                MessageBusInterface::class              => Container\MessageBusFactory::class,
                PsrContext::class                       => Container\RedisFactory::class,
                SendMessageMiddleware::class            => Container\SendMessageMiddlewareFactory::class,
                SerializerInterface::class              => Container\SerializerFactory::class,
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
        ];
    }
}
