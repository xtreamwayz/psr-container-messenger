<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Mailer\Messenger\SendEmailMessage;

return [
    // phpcs:disable
    'dependencies' => [
        'factories' => [
            'messenger.transport.commands' => [TransportFactory::class, 'commandqueue'],
        ],
    ],

    'messenger' => [
        'transports' => [
            'commandqueue' => [
                'dsn' => 'redis://localhost:6379/commandqueue',
                'options' => [
                    'autosetup' => false,
                ],
            ],
        ],
        'buses'       => [
            'messenger.command.bus' => [
                'allows_no_handler' => false,
                'handlers'          => [
                    Domain\User\Command\ChangeUserPassword::class => [Domain\User\Command\Handler\ChangeUserPasswordHandler::class],
                ],
                'middleware'        => [
                    Infrastructure\Messenger\ValidationMiddleware::class,
                    'messenger.command.middleware.sender',
                    'messenger.command.middleware.handler',
                ],
                'routes'            => [
                    Domain\User\Command\UpdateAccount::class => ['messenger.transport.commands'],
                ],
            ],
        ],
    ],
    // phpcs:enable
];
