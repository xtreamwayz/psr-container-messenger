<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class DecoderFactory
{
    public function __invoke(ContainerInterface $container) : DecoderInterface
    {
        return new Serializer($container->get(SerializerInterface::class), 'json');
    }
}
