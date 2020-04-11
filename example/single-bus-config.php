<?php

declare(strict_types=1);

namespace App;

use Xtreamwayz\PsrContainerMessenger\Container\HandleMessageMiddlewareFactory;
use Xtreamwayz\PsrContainerMessenger\Container\MessageBusFactory;
use Xtreamwayz\PsrContainerMessenger\Container\SendMessageMiddlewareFactory;

return [
    // phpcs:disable
    'dependencies' => [
        'factories' => [
            'messenger.default.bus'                => [MessageBusFactory::class, 'default'],
            'messenger.default.middleware.handler' => [HandleMessageMiddlewareFactory::class, 'default'],
            'messenger.default.middleware.sender'  => [SendMessageMiddlewareFactory::class, 'default'],
        ],
    ],

    'messenger' => [
        'default_bus' => 'default',
        'buses'       => [
            'default' => [
                'allows_no_handler' => false,
                'handlers'          => [],
                'middleware'        => [
                    'messenger.default.middleware.sender',
                    'messenger.default.middleware.handler',
                ],
                'routes'            => [],
            ],
        ],
    ],
    // phpcs:enable
];
