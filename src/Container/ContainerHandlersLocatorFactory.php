<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;

final class ContainerHandlersLocatorFactory
{
    /** @var string */
    private $busName;

    public function __construct(string $busName = 'messenger.default.bus')
    {
        $this->busName = $busName;
    }

    public function __invoke(ContainerInterface $container) : HandlersLocatorInterface
    {
        $config   = $container->has('config') ? $container->get('config') : [];
        $config   = $config['messenger']['buses'][$this->busName] ?? [];
        $handlers = $config['handlers'] ?? [];

        return new ContainerHandlersLocator($container, $handlers);
    }
}
