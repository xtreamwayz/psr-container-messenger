<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Xtreamwayz\PsrContainerMessenger\Serializer\SymfonySerializerFactory;

return [
    // phpcs:disable
    'dependencies' => [
        'aliases' => [
            'messenger.serializer' => Serializer::class,
        ],
        'factories' => [
            Serializer::class => SymfonySerializerFactory::class,
        ]
    ],

    'messenger' => [
        'serializer' => [
            'default_serializer' => null,
            'symfony_serializer' => [
                'format' => 'json',
                'context' => [],
            ],
        ],
        'default_bus' => 'messenger.command.bus',
        'buses'       => [
            'messenger.command.bus' => [
                'allows_no_handler' => false,
                'handlers'          => [],
                'middleware'        => [
                    'messenger.command.middleware.add_bus_stamp',
                    'messenger.command.middleware.handler',
                ],
                'routes'            => [],
            ],
            'messenger.event.bus'   => [
                'allows_no_handler' => true,
                'handlers'          => [],
                'middleware'        => [
                    'messenger.event.middleware.handler',
                ],
                'routes'            => [],
            ],
            'messenger.query.bus'   => [
                'allows_no_handler' => false,
                'handlers'          => [],
                'middleware'        => [
                    'messenger.query.middleware.handler',
                ],
                'routes'            => [],
            ],
        ],
    ],
    // phpcs:enable
];
