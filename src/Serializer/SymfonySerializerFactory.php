<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Serializer;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

final class SymfonySerializerFactory
{
    public function __invoke(ContainerInterface $container, ?string $requestedName = null): Serializer
    {
        $config  = $container->has('config') ? $container->get('config') : [];
        $config  = $config['messenger']['serializer'] ?? [];
        $default = $config['default_serializer'] ?? null;
        $config  = $config['symfony_serializer'] ?? [];

        return new Serializer($default, $config['format'] ?? 'json', $config['context'] ?? []);
    }
}
