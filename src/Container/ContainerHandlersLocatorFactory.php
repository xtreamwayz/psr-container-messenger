<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use function array_combine;
use function array_keys;
use function array_map;
use function is_array;
use function is_string;

final class ContainerHandlersLocatorFactory
{
    /** @var string */
    private $busName;

    public function __construct(string $busName = 'messenger.bus.default')
    {
        $this->busName = $busName;
    }

    public function __invoke(ContainerInterface $container) : HandlersLocator
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['messenger']['buses'][$this->busName] ?? [];

        $handlers = $config['handlers'] ?? [];

        $handlersCallables = array_map(function ($handlers) use ($container) {
            $wrapper = function (string $handlerId) use ($container) {
                return function ($message) use ($container, $handlerId) {
                    $handler = $container->get($handlerId);

                    return $handler($message);
                };
            };

            if (is_string($handlers)) {
                $handlers = [$handlers];
            }

            if (is_array($handlers)) {
                return array_map($wrapper, $handlers);
            }

            throw new \InvalidArgumentException('Handlers must be an array or string');
        }, $handlers);

        return new HandlersLocator(array_combine(array_keys($handlers), $handlersCallables));
    }
}
