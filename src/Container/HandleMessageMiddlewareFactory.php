<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Container;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

use function sprintf;

class HandleMessageMiddlewareFactory
{
    private string $busName;

    public static function __callStatic(string $busName, array $arguments): MiddlewareInterface
    {
        if (! isset($arguments[0]) || ! $arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(
                sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }

        return (new self($busName))($arguments[0]);
    }

    public function __construct(string $busName = 'messenger.default.bus')
    {
        $this->busName = $busName;
    }

    public function __invoke(ContainerInterface $container): MiddlewareInterface
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $logger = $config['messenger']['logger'] ?? null;
        $config = $config['messenger']['buses'][$this->busName] ?? [];

        $allowNoHandlers = $config['allows_no_handler'] ?? false;

        $handlerLocator = (new ContainerHandlersLocatorFactory($this->busName))($container);
        $middleware     = new HandleMessageMiddleware($handlerLocator, $allowNoHandlers);

        if ($logger !== null) {
            $middleware->setLogger($container->get($logger));
        }

        return $middleware;
    }
}
