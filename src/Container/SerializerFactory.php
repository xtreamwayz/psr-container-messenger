<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerFactory
{
    public function __invoke(ContainerInterface $container) : SerializerInterface
    {
        $encoders    = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new PropertyNormalizer(null, new CamelCaseToSnakeCaseNameConverter())];

        return new Serializer($normalizers, $encoders);
    }
}
