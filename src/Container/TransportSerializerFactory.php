<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class TransportSerializerFactory
{
    public function __invoke(ContainerInterface $container) : Serializer
    {
        return new Serializer($container->get(SerializerInterface::class), 'json');
    }
}
