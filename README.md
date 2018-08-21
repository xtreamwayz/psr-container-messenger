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
configuration is added automatically for you.

## Command, Query and Event buses

By default there are 3 buses registered.

```php
// Each dispatched command must have one handler. 
$commandBus = $container->get('messenger.bus.command');

// Each dispatched event may have zero or more handlers.
$eventBus = $container->get('messenger.bus.event');

// Each dispatched query must have one handler and returns a result.
$queryBus = $container->get('messenger.bus.query');
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
            new MyMessage(['foo' => 'bar'])
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

use App\Domain\Command\MyCommand;
use App\Domain\Handler\MyCommandHandler;
use App\Domain\Handler\MyCommandHandlerFactory;
use App\Domain\Query\MyQuery;
use App\Domain\Handler\MyQueryHandler;
use App\Domain\Handler\MyQueryHandlerFactory;
use App\Domain\Event\MyEvent;
use App\Domain\Handler\MyEventHandler;
use App\Domain\Handler\MyEventHandlerFactory;

return [
    'dependencies' => [
        'factories' => [
            MyEventHandler::class   => MyEventHandlerFactory::class,
            MyCommandHandler::class => MyCommandHandlerFactory::class,
            MyQueryHandler::class   => MyQueryHandlerFactory::class,
        ],
    ],

    'messenger' => [
        'default_bus'        => 'messenger.bus.command',
        'default_middleware' => true,
        'buses'              => [
            // Command bus
            'messenger.bus.command' => [
                'handlers'   => [
                    // A command must have one handler
                    MyCommand::class => MyCommandHandler::class
                ],
                'middleware' => [
                    // Add custom middleware    
                ],
                'routes'     => [
                    // Transport routes to senders (queue, 3rd party, https endpoint)
                    MyCommand::class => 'messenger.bus.command'
                ],
            ],
            // Event bus
            'messenger.bus.event'   => [
                'handlers'   => [
                    // An event may have multiple handlers
                    MyEvent::class => MyEventHandler::class,  
                    AnotherEvent::class => [
                        AnotherEventHandler::class,
                        SecondAnotherEventHandler::class,
                    ],  
                ],
                'middleware' => [
                    AllowNoHandlerMiddleware::class,
                    // Add custom middleware    
                ],
                'routes'     => [
                    // Transport routes to senders (queue, 3rd party, https endpoint)
                ],
            ],
            'messenger.bus.query'   => [
                'handlers'   => [
                    MyQuery::class => MyQueryHandler::class
                ],
                'middleware' => [
                    // Add custom middleware
                ],
                'routes'     => [
                    // Transport routes to senders (queue, 3rd party, https endpoint)
                ],
            ],
        ],
    ],
];
```
