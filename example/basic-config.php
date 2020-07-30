<?php

declare(strict_types=1);

namespace App;

return [
    // phpcs:disable
    'dependencies' => [
    ],

    'messenger' => [
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
