<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger;

use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Xtreamwayz\Expressive\Messenger\Container\HandleMessageMiddlewareFactory;
use Xtreamwayz\Expressive\Messenger\Container\MessageBusFactory;
use Xtreamwayz\Expressive\Messenger\Container\SendMessageMiddlewareFactory;

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
        return [
            'factories' => [
                ConsumeMessagesCommand::class => Command\ConsumeMessagesCommandFactory::class,

                'messenger.command.bus'                => [MessageBusFactory::class, 'messenger.command.bus'],
                'messenger.command.middleware.handler' => [
                    HandleMessageMiddlewareFactory::class,
                    'messenger.command.bus',
                ],
                'messenger.command.middleware.sender'  => [
                    SendMessageMiddlewareFactory::class,
                    'messenger.command.bus',
                ],

                'messenger.event.bus'                => [MessageBusFactory::class, 'messenger.event.bus'],
                'messenger.event.middleware.handler' => [HandleMessageMiddlewareFactory::class, 'messenger.event.bus'],
                'messenger.event.middleware.sender'  => [SendMessageMiddlewareFactory::class, 'messenger.event.bus'],

                'messenger.query.bus'                => [MessageBusFactory::class, 'messenger.query.bus'],
                'messenger.query.middleware.handler' => [HandleMessageMiddlewareFactory::class, 'messenger.query.bus'],
                'messenger.query.middleware.sender'  => [SendMessageMiddlewareFactory::class, 'messenger.query.bus'],
            ],
        ];
    }

    public function getMessenger() : array
    {
        return [
            'default_bus'        => 'messenger.command.bus',
            'default_middleware' => true,
            'buses'              => [
                'messenger.command.bus' => [
                    'allows_no_handler' => false,
                    'handlers'          => [],
                    'middleware'        => [
                        'messenger.command.middleware.sender',
                        'messenger.command.middleware.handler',
                    ],
                    'routes'            => [],
                ],
                'messenger.event.bus'   => [
                    'allows_no_handler' => true,
                    'handlers'          => [],
                    'middleware'        => [
                        'messenger.event.middleware.sender',
                        'messenger.event.middleware.handler',
                    ],
                    'routes'            => [],
                ],
                'messenger.query.bus'   => [
                    'allows_no_handler' => false,
                    'handlers'          => [],
                    'middleware'        => [
                        'messenger.query.middleware.sender',
                        'messenger.query.middleware.handler',
                    ],
                    'routes'            => [],
                ],
            ],
        ];
    }

    public function getConsole() : array
    {
        return [
            'commands' => [
                'messenger:consume' => ConsumeMessagesCommand::class,
            ],
        ];
    }
}
