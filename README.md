# Expressive Messenger

_Message bus and queue for Zend Expressive with Symfony Messenger + Enqueue_

[![Build Status](https://travis-ci.com/xtreamwayz/expressive-messenger.svg)](https://travis-ci.com/xtreamwayz/expressive-messenger)
[![Packagist](https://img.shields.io/packagist/v/xtreamwayz/expressive-messenger.svg)](https://packagist.org/packages/xtreamwayz/expressive-messenger)
[![Packagist](https://img.shields.io/packagist/vpre/xtreamwayz/expressive-messenger.svg)](https://packagist.org/packages/xtreamwayz/expressive-messenger)

This packages brings message buses to your Zend Expressive project. Basically it's a bundle of factories to make
life easier for you. The real work is done by [Symfony Messenger](https://github.com/symfony/messenger)
and [enqueue](https://github.com/php-enqueue/enqueue).

It comes with pre-configured command, event and query buses for your convenience. Or don't use them if you want to
create your own. Transports are used to queue your messages or send and receive them to/from 3rd parties.

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

## Configuration

```php
<?php

declare(strict_types=1);

namespace App;

use App\Domain\Command\RegisterUser;
use App\Domain\Event\UserRegistered;
use App\Domain\Handler\FindUserHandler;
use App\Domain\Handler\FindUserHandlerFactory;
use App\Domain\Handler\RegisterUserHandler;
use App\Domain\Handler\RegisterUserHandlerFactory;
use App\Domain\Handler\UserRegisteredHandler;
use App\Domain\Handler\UserRegisteredHandlerFactory;
use App\Domain\Query\FindUser;
use Xtreamwayz\Expressive\Messenger\Transport\EnqueueTransportFactory;

return [
    'dependencies' => [
        'factories' => [
            FindUserHandler::class       => FindUserHandlerFactory::class,
            RegisterUserHandler::class   => RegisterUserHandlerFactory::class,
            UserRegisteredHandler::class => UserRegisteredHandlerFactory::class,

            'messenger.transport.redis'   => [EnqueueTransportFactory::class, 'redis:'],
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
                    RegisterUser::class => RegisterUserHandler::class
                ],
                'middleware' => [
                    // Add custom middleware
                ],
                'routes'     => [
                    // Transport routes to senders (queue, 3rd party, https endpoint)
                    RegisterUser::class => 'messenger.transport.redis'
                ],
            ],
            // Event bus
            'messenger.bus.event'   => [
                'handlers'   => [
                    // An event may have multiple handlers
                    UserRegistered::class => [
                        SendTermsEmailHandler::class,
                        SendActivationEmailHandler::class,
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
                    FindUser::class => FindUserHandler::class
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

### Using the bus in your middleware or handler

```php
<?php

declare(strict_types=1);

namespace App\Handler;

use App\Domain\Command\RegisterUser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;
use Zend\Diactoros\Response\JsonResponse;

class RegisterHandler implements RequestHandlerInterface
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
            new RegisterUser([
                'id'       => Uuid::uuid4()->toString(),
                'email'    => $request->getAttribute('email'),
                'password' => $request->getAttribute('password'),
            ])
        );

        return new JsonResponse([], 204);
    }
}
```
