<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Container;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;

use function sprintf;

final class AddBusNameStampMiddlewareFactory
{
    private string $busName;

    private function __construct(string $busName)
    {
        $this->busName = $busName;
    }

    public static function __callStatic(string $name, array $arguments): AddBusNameStampMiddleware
    {
        if (! isset($arguments[0]) || ! $arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(
                sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }

        return (new static($name))->__invoke($arguments[0]);
    }

    public function __invoke(ContainerInterface $container): AddBusNameStampMiddleware
    {
        if (! $container->has($this->busName)) {
            throw new InvalidArgumentException(sprintf('No service with name %s found', $this->busName));
        }

        return new AddBusNameStampMiddleware($this->busName);
    }
}
