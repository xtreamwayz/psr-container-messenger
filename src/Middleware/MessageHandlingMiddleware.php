<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Middleware;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use function get_class;
use function is_array;
use function sprintf;

class MessageHandlingMiddleware implements MiddlewareInterface
{
    /** @var ContainerInterface */
    private $handlerResolver;

    /** @var array */
    private $messageHandlers;

    public function __construct(ContainerInterface $handlerResolver, array $messageHandlers)
    {
        $this->handlerResolver = $handlerResolver;
        $this->messageHandlers = $messageHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($message, callable $next)
    {
        $messageClass = get_class($message);
        if (! isset($this->messageHandlers[$messageClass])) {
            throw new NoHandlerForMessageException(sprintf('No handler for message "%s".', $messageClass));
        }

        if (! is_array($this->messageHandlers[$messageClass])) {
            $handler = $this->handlerResolver->get($this->messageHandlers[$messageClass]);
            $result  = $handler($message);

            $next($message);

            return $result;
        }

        $result = [];
        foreach ((array) $this->messageHandlers[$messageClass] as $handlerClass) {
            $handler  = $this->handlerResolver->get($handlerClass);
            $result[] = $handler($message);
        }

        $next($message);

        return $result;
    }
}
