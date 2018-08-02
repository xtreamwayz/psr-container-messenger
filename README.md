# Expressive Messenger

_Symfony Messenger + Enqueue for Zend Expressive_

[![Build Status](https://travis-ci.org/xtreamwayz/expressive-messenger.svg)](https://travis-ci.org/xtreamwayz/expressive-messenger)
[![Packagist](https://img.shields.io/packagist/v/xtreamwayz/expressive-messenger.svg)](https://packagist.org/packages/xtreamwayz/expressive-messenger)
[![Packagist](https://img.shields.io/packagist/vpre/xtreamwayz/expressive-messenger.svg)](https://packagist.org/packages/xtreamwayz/expressive-messenger)

This packages brings a command bus and optional queue to your Zend Expressive project. Basically it's a bundle of
factories to make life easier for you. The real work is done by [Symfony Messenger](https://github.com/symfony/messenger)
and [enqueue](https://github.com/php-enqueue/enqueue).

## Installation

    composer require xtreamwayz/expressive-messenger

If you have the [zend-component-installer](https://github.com/zendframework/zend-component-installer) installed, the
ConfigProvider is installed automatically.

## Command Bus

```php
<?php

declare(strict_types=1);

namespace App;

use App\Handler\MyMessageHandlerFactory;
use App\Message\MyMessage;
use Xtreamwayz\Expressive\Messenger\Queue\QueueReceiverFactory;
use Xtreamwayz\Expressive\Messenger\Queue\QueueSenderFactory;

return [
    'dependencies' => [
        'factories' => [
            'handler.' . MyMessage::class => MyMessageHandlerFactory::class,
        ],
    ],

    'messenger' => [
        'middleware' => [
            // These middleware are added by default
        ],

        'routing' => [
            // These are loaded into the SendMessageMiddleware
        ],
    ]
];
```

### Using the command bus

```php
<?php

declare(strict_types=1);

namespace App\Handler;

use App\Message\MyMessage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Zend\Diactoros\Response\JsonResponse;

class TestHandler implements RequestHandlerInterface
{
    /** @var MessageBusInterface */
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $this->bus->dispatch(
            new Envelope(new MyMessage(['foo' => 'bar']))
        );

        return new JsonResponse([], 204);
    }
}
```

## Command Bus with Queue

```php
<?php

declare(strict_types=1);

namespace App;

use App\Handler\MyMessageHandlerFactory;
use App\Message\MyMessage;
use Symfony\Component\Messenger\Asynchronous\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Xtreamwayz\Expressive\Messenger\Queue\QueueTransportFactory;

return [
    'dependencies' => [
        'factories' => [
            'handler.' . MyMessage::class => MyMessageHandlerFactory::class,

            // This queue is added by default
            'messenger.transport.default' => [QueueTransportFactory::class, 'messenger.transport.default'],

            // Add a second queue
            'messenger.transport.commands' => [QueueTransportFactory::class, 'messenger.transport.commands'],
            'messenger.transport.events'   => [QueueTransportFactory::class, 'messenger.transport.events'],
        ],
    ],

    'messenger' => [
        'middleware' => [
            // These middleware are added by default
            SendMessageMiddleware::class,
            HandleMessageMiddleware::class,
        ],

        'routing' => [
            // These are loaded into the SendMessageMiddleware
            MyMessage::class => 'messenger.transport.default',
        ],
    ]
];
```
