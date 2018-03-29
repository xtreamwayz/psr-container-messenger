<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class EncoderFactory
{
    public function __invoke(ContainerInterface $container) : EncoderInterface
    {
        return new Serializer($container->get(SerializerInterface::class), 'json');
    }
}
