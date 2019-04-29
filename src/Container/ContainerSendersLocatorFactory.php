<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;

final class ContainerSendersLocatorFactory
{
    /** @var string */
    private $busName;

    public function __construct(string $busName = 'messenger.bus.default')
    {
        $this->busName = $busName;
    }

    public function __invoke(ContainerInterface $container) : SendersLocatorInterface
    {
        $config  = $container->has('config') ? $container->get('config') : [];
        $config  = $config['messenger']['buses'][$this->busName] ?? [];
        $senders = $config['routes'] ?? [];
        $sendAndHandle = $config['send_and_handle'] ?? [];

        return new ContainerSendersLocator($container, $senders, $sendAndHandle);
    }
}
