<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Asynchronous\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Asynchronous\Routing\SenderLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\LoggingMiddleware;
use Xtreamwayz\Expressive\Messenger\Exception\InvalidConfigException;
use Xtreamwayz\Expressive\Messenger\Middleware\MessageHandlingMiddleware;
use function sprintf;

class MessageBusFactory
{
    /** @var string */
    private $name;

    /**
     * Creates a new instance from a specified config
     *
     * <code>
     * <?php
     * return [
     *     'messenger.bus.default' => [MessageBusFactory::class, 'messenger.bus.default'],
     * ];
     * </code>
     *
     * @throws InvalidArgumentException
     */
    public static function __callStatic(string $dsn, array $arguments) : MessageBusInterface
    {
        if (! isset($arguments[0]) || ! $arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(
                sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }

        return (new self($dsn))($arguments[0]);
    }

    public function __construct(?string $name = null)
    {
        $this->name = $name ?? 'messenger.bus.default';
    }

    public function __invoke(ContainerInterface $container) : MessageBusInterface
    {
        $config            = $container->has('config') ? $container->get('config') : [];
        $debug             = $config['debug'] ?? false;
        $defaultMiddleware = $config['messenger']['default_middleware'] ?? false;
        $handlers          = $config['messenger']['buses'][$this->name]['handlers'] ?? [];
        $middlewares       = $config['messenger']['buses'][$this->name]['middleware'] ?? [];
        $routes            = $config['messenger']['buses'][$this->name]['routes'] ?? [];

        $stack = [];
        // Add default logging middleware
        if ($debug === true && $defaultMiddleware === true) {
            $stack[] = $container->get(LoggingMiddleware::class);
        }

        // Add middleware from configuration
        foreach ($middlewares as $middleware) {
            $stack[] = $container->get($middleware);
        }

        // Add default sender middleware
        if ($routes && $defaultMiddleware === true) {
            $stack[] = new SendMessageMiddleware(
                new SenderLocator($container, $routes ?? [])
            );
        }

        // Add default message handling middleware
        if ($defaultMiddleware === true) {
            $stack[] = new MessageHandlingMiddleware($container, $handlers);
        }

        if (empty($stack)) {
            throw new InvalidConfigException(
                'Without middleware, messenger does not do anything!'
            );
        }

        return new MessageBus($stack);
    }
}
