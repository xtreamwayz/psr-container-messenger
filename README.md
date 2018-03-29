# Expressive Messenger

_Symfony Messenger + Enqueue for Zend Expressive_

This packages brings a command bus and optional queue to your Zend Expressive project. Basically it's a bundle of 
factories to make life easier for you. The real work is done by [Symfony Messenger](https://github.com/symfony/messenger)
and [enqueue](https://github.com/php-enqueue/enqueue).

## Installation

    composer require xtreamlabs/expressive-messenger
    
If you have the [zend-component-installer](https://github.com/zendframework/zend-component-installer) installed, the
ConfigProvider is installed automatically.

## Command Bus

```php
<?php

declare(strict_types=1);

namespace App;

use App\Handler\MyMessageHandlerFactory;
use App\Message\MyMessage;
use XtreamLabs\Expressive\Messenger\Queue\QueueReceiverFactory;
use XtreamLabs\Expressive\Messenger\Queue\QueueSenderFactory;

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

        'senders' => [
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

use App\Handler\MyMessageHandlerFactory;
use App\Message\MyMessage;
use Symfony\Component\Messenger\Asynchronous\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use XtreamLabs\Expressive\Messenger\Queue\QueueReceiverFactory;
use XtreamLabs\Expressive\Messenger\Queue\QueueSenderFactory;

return [
    'dependencies' => [
        'factories' => [
            'handler.' . MyMessage::class => MyMessageHandlerFactory::class,

            // This queue is added by default
            'queue.default.receiver' => [QueueReceiverFactory::class, 'queue.default'],
            'queue.default.sender'   => [QueueSenderFactory::class, 'queue.default'],
            
            // Add a second queue
            'queue.another.receiver' => [QueueReceiverFactory::class, 'queue.another'],
            'queue.another.sender'   => [QueueSenderFactory::class, 'queue.another'],
        ],
    ],

    'messenger' => [
        'middleware' => [
            // These middleware are added by default
            SendMessageMiddleware::class,
            HandleMessageMiddleware::class,
        ],

        'senders' => [
            // These are loaded into the SendMessageMiddleware
            MyMessage::class => ['queue.default.sender'],
        ],
    ]
];
```
