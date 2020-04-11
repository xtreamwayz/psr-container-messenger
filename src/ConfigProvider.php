<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger;

use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Xtreamwayz\PsrContainerMessenger\Container\HandleMessageMiddlewareFactory;
use Xtreamwayz\PsrContainerMessenger\Container\MessageBusFactory;
use Xtreamwayz\PsrContainerMessenger\Container\SendMessageMiddlewareFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'messenger'    => $this->getMessenger(),
            'console'      => $this->getConsole(),
        ];
    }

    public function getDependencies(): array
    {
        // phpcs:disable
        return [
            'factories' => [
                ConsumeMessagesCommand::class => Command\ConsumeMessagesCommandFactory::class,

                'messenger.command.bus'                => [MessageBusFactory::class, 'messenger.command.bus'],
                'messenger.command.middleware.handler' => [HandleMessageMiddlewareFactory::class, 'messenger.command.bus'],
                'messenger.command.middleware.sender'  => [SendMessageMiddlewareFactory::class, 'messenger.command.bus'],

                'messenger.event.bus'                => [MessageBusFactory::class, 'messenger.event.bus'],
                'messenger.event.middleware.handler' => [HandleMessageMiddlewareFactory::class, 'messenger.event.bus'],
                'messenger.event.middleware.sender'  => [SendMessageMiddlewareFactory::class, 'messenger.event.bus'],

                'messenger.query.bus'                => [MessageBusFactory::class, 'messenger.query.bus'],
                'messenger.query.middleware.handler' => [HandleMessageMiddlewareFactory::class, 'messenger.query.bus'],
                'messenger.query.middleware.sender'  => [SendMessageMiddlewareFactory::class, 'messenger.query.bus'],
            ],
        ];
        // phpcs:enable
    }

    public function getMessenger(): array
    {
        return [
            'default_bus' => 'messenger.command.bus',
            'buses'       => [
                'messenger.command.bus' => [
                    'allows_no_handler' => false,
                    'handlers'          => [],
                    'middleware'        => [],
                    'routes'            => [],
                ],
                'messenger.event.bus'   => [
                    'allows_no_handler' => true,
                    'handlers'          => [],
                    'middleware'        => [],
                    'routes'            => [],
                ],
                'messenger.query.bus'   => [
                    'allows_no_handler' => false,
                    'handlers'          => [],
                    'middleware'        => [],
                    'routes'            => [],
                ],
            ],
        ];
    }

    public function getConsole(): array
    {
        return [
            'commands' => [
                'messenger:consume' => ConsumeMessagesCommand::class,
            ],
        ];
    }
}
