<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use function class_implements;
use function class_parents;
use function get_class;
use function in_array;

class ContainerHandlersLocator implements HandlersLocatorInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var string[] */
    private $handlers;

    /**
     * @param string[] $handlers (MessageHandlerInterface)
     */
    public function __construct(ContainerInterface $container, array $handlers)
    {
        $this->container = $container;
        $this->handlers  = $handlers;
    }

    public function getHandlers(Envelope $envelope) : iterable
    {
        $seen = [];

        foreach (self::listTypes($envelope) as $type) {
            foreach ($this->handlers[$type] ?? [] as $alias => $handler) {
                if (! in_array($handler, $seen, true)) {
                    yield $alias => $seen[] = $this->container->get($handler);
                }
            }
        }
    }

    private static function listTypes(Envelope $envelope) : array
    {
        $class = get_class($envelope->getMessage());

        return [$class => $class]
            + class_parents($class)
            + class_implements($class)
            + ['*' => '*'];
    }
}
