<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use function array_combine;
use function array_keys;
use function array_map;
use function is_array;
use function is_string;

final class ContainerSendersLocatorFactory
{
    /** @var string */
    private $busName;

    public function __construct(string $busName = 'messenger.bus.default')
    {
        $this->busName = $busName;
    }

    public function __invoke(ContainerInterface $container) : SendersLocator
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['messenger']['buses'][$this->busName] ?? [];

        $senders = $config['routes'] ?? [];

        $sendersCallables = array_map(function ($senders) use ($container) {
            $wrapper = function (string $senderId) use ($container) {
                return function ($message) use ($container, $senderId) {
                    $handler = $container->get($senderId);

                    return $handler($message);
                };
            };

            if (is_string($senders)) {
                $senders = [$senders];
            }

            if (is_array($senders)) {
                return array_map($wrapper, $senders);
            }

            throw new \InvalidArgumentException('Senders must be an array or string');
        }, $senders);

        return new SendersLocator(
            array_combine(array_keys($senders), $sendersCallables),
            []
        );
    }
}
