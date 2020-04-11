<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use function class_implements;
use function class_parents;
use function get_class;
use function in_array;
use function is_callable;

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
            foreach ($this->handlers[$type] ?? [] as $handlerDescriptor) {
                if (is_callable($handlerDescriptor)) {
                    $handlerDescriptor = new HandlerDescriptor($handlerDescriptor);
                } elseif ($this->container->has($handlerDescriptor)) {
                    $handlerDescriptor = new HandlerDescriptor($this->container->get($handlerDescriptor));
                }

                if (! $this->shouldHandle($envelope, $handlerDescriptor)) {
                    continue;
                }

                $name = $handlerDescriptor->getName();
                if (in_array($name, $seen, true)) {
                    continue;
                }

                $seen[] = $name;

                yield $handlerDescriptor;
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

    private function shouldHandle(Envelope $envelope, HandlerDescriptor $handlerDescriptor) : bool
    {
        /** @var ReceivedStamp|null $received */
        $received = $envelope->last(ReceivedStamp::class);
        if ($received === null) {
            return true;
        }

        $expectedTransport = $handlerDescriptor->getOption('from_transport');
        if ($expectedTransport === null) {
            return true;
        }

        return $received->getTransportName() === $expectedTransport;
    }
}
