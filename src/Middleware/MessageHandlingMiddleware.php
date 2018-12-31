<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Middleware;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use function get_class;
use function is_array;
use function sprintf;

class MessageHandlingMiddleware implements MiddlewareInterface
{
    /** @var ContainerInterface */
    private $handlerResolver;

    /** @var array */
    private $messageHandlers;

    /** @var bool */
    private $allowsNoHandler;

    public function __construct(ContainerInterface $handlerResolver, array $messageHandlers, bool $allowsNoHandler)
    {
        $this->handlerResolver = $handlerResolver;
        $this->messageHandlers = $messageHandlers;
        $this->allowsNoHandler = $allowsNoHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Envelope $envelope, StackInterface $stack) : Envelope
    {
        $message = $envelope->getMessage();

        $messageClass = get_class($message);
        if (! isset($this->messageHandlers[$messageClass])) {
            if (! $this->allowsNoHandler) {
                throw new NoHandlerForMessageException(sprintf('No handler for message "%s".', $messageClass));
            }

            $stack->next()->handle($envelope, $stack);

            return $envelope;
        }

        if (! is_array($this->messageHandlers[$messageClass])) {
            $handler = $this->handlerResolver->get($this->messageHandlers[$messageClass]);
            $result  = $handler($message);

            $stack->next()->handle($envelope, $stack);

            return $envelope->with(HandledStamp::fromCallable($handler, $result));
        }

        $result = [];
        foreach ($this->messageHandlers[$messageClass] as $handlerClass) {
            $handler  = $this->handlerResolver->get($handlerClass);
            $result[] = HandledStamp::fromCallable($handler, $handler($message));
        }

        $stack->next()->handle($envelope, $stack);

        return $envelope->with(...$result);
    }
}
