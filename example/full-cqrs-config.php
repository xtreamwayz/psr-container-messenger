<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Mailer\Messenger\MessageHandler;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;

return [
    // phpcs:disable
    'dependencies' => [
        'factories' => [
            'messenger.transport.commands' => [TransportFactory::class, 'redis://localhost:6379/commandqueue'],
            'messenger.transport.emails'   => [TransportFactory::class, 'redis://localhost:6379/emailqueue'],
            'messenger.transport.events'   => [TransportFactory::class, 'redis://localhost:6379/eventqueue'],

            Infrastructure\Messenger\ValidationMiddleware::class => Infrastructure\Messenger\ValidationMiddlewareFactory::class,
        ],
    ],

    'messenger' => [
        'default_bus' => 'messenger.command.bus',
        'buses'       => [
            'messenger.command.bus' => [
                'allows_no_handler' => false,
                'handlers'          => [
                    Domain\User\Command\ChangeUserPassword::class => [Domain\User\Command\Handler\ChangeUserPasswordHandler::class],
                    Domain\User\Command\ForgotPassword::class     => [Domain\User\Command\Handler\ForgotPasswordHandler::class],
                    Domain\User\Command\RegisterUser::class       => [Domain\User\Command\Handler\RegisterUserHandler::class],
                    Domain\User\Command\UpdateAccount::class      => [Domain\User\Command\Handler\UpdateAccountHandler::class],
                ],
                'middleware'        => [
                    Infrastructure\Messenger\ValidationMiddleware::class,
                    'messenger.command.middleware.sender',
                    'messenger.command.middleware.handler',
                ],
                'routes'            => [
                    Domain\User\Command\UpdateAccount::class => ['messenger.transport.commands'],
                    SendEmailMessage::class                  => ['messenger.transport.emails'],
                ],
            ],
            'messenger.event.bus'   => [
                'allows_no_handler' => true,
                'handlers'          => [
                    Domain\User\Event\UserRegistered::class      => [Domain\User\Event\Handler\SendEmailWhenUserRegistered::class],
                    Domain\User\Event\UserSignedIn::class        => [Domain\User\Event\Handler\RecordThatUserSignedIn::class],
                    Domain\User\Event\UserSigninFailed::class    => [Domain\User\Event\Handler\RecordThatUserSigninFailed::class],
                    Domain\User\Event\UserPasswordChanged::class => [Domain\User\Event\Handler\SendEmailWhenUserPasswordChanged::class],
                    Domain\User\Event\UserProfileChanged::class  => [Domain\User\Event\Handler\SendEmailWhenUserProfileChanged::class],
                ],
                'middleware'        => [
                    'messenger.event.middleware.sender',
                    'messenger.event.middleware.handler',
                ],
                'routes'            => [
                    '*' => ['messenger.transport.events']
                ],
            ],
            'messenger.query.bus'   => [
                'allows_no_handler' => false,
                'handlers'          => [
                    Domain\User\Query\FindUserByEmail::class => [Domain\User\Query\Handler\FindUserByEmailHandler::class],
                    Domain\User\Query\FindUserById::class    => [Domain\User\Query\Handler\FindUserByIdHandler::class],
                    Domain\User\Query\FindUsers::class       => [Domain\User\Query\Handler\FindUsersHandler::class],
                ],
                'middleware'        => [
                    // 'messenger.query.middleware.sender',
                    'messenger.query.middleware.handler',
                ],
                'routes'            => [],
            ],
        ],
    ],
    // phpcs:enable
];
