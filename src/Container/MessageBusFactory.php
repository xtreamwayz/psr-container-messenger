<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Xtreamwayz\Expressive\Messenger\Exception\InvalidConfigException;
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
        $this->name = $name ?? 'messenger.default.bus';
    }

    public function __invoke(ContainerInterface $container) : MessageBusInterface
    {
        $config      = $container->has('config') ? $container->get('config') : [];
        $middlewares = $config['messenger']['buses'][$this->name]['middleware'] ?? [];

        $stack = [];

        // Add middleware from configuration
        foreach ($middlewares as $middleware) {
            $stack[] = $container->get($middleware);
        }

        if (empty($stack)) {
            throw new InvalidConfigException('Without middleware, messenger does not do anything');
        }

        return new MessageBus($stack);
    }
}
