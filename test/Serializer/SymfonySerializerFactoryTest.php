<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test\Container;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Xtreamwayz\PsrContainerMessenger\ConfigProvider;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

use function array_replace_recursive;

class SymfonySerializerFactoryTest extends TestCase
{
    private array $config;

    public function setUp(): void
    {
        $this->config = array_replace_recursive((new ConfigProvider())(), require 'example/basic-config.php');
    }

    private function getContainer(): ServiceManager
    {
        $container = new ServiceManager();
        (new Config($this->config['dependencies']))->configureServiceManager($container);
        $container->setService('config', $this->config);

        return $container;
    }

    public function testSymfonySerializerIsLoaded(): void
    {
        $this->config['dependencies']['aliases']['messenger.serializer'] = Serializer::class;

        $serializer = $this->getContainer()->get('messenger.serializer');

        $this->assertInstanceOf(Serializer::class, $serializer);
    }
}
