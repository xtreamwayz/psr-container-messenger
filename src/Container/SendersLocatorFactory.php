<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;

final class SendersLocatorFactory
{
    /** @var string */
    private $busName;

    public function __construct(string $busName = 'messenger.default.bus')
    {
        $this->busName = $busName;
    }

    public function __invoke(ContainerInterface $container): SendersLocatorInterface
    {
        $config     = $container->has('config') ? $container->get('config') : [];
        $sendersMap = $config['messenger']['buses'][$this->busName]['routes'] ?? [];

        return new SendersLocator($sendersMap, $container);
    }
}
