<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test\Integration;

use Laminas\AuraDi\Config\Config;
use Laminas\AuraDi\Config\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Xtreamwayz\PsrContainerMessenger\ConfigProvider;

use function array_replace_recursive;
use function is_array;
use function sprintf;

class AuraDiTest extends TestCase
{
    private ConfigProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray(): void
    {
        $config = ($this->provider)();

        $this->assertNotEmpty($config);
    }

    public function testServicesDefinedInConfigProvider(): void
    {
        $config = array_replace_recursive(
            ($this->provider)(),
            require 'example/basic-config.php'
        );

        /** @var ContainerInterface $container */
        $container = (new ContainerFactory())(new Config($config));

        foreach ($config['dependencies']['aliases'] as $name => $factory) {
            $this->assertContainerHasService($container, $name, $factory);
        }

        foreach ($config['dependencies']['invokables'] as $name => $factory) {
            $this->assertContainerHasService($container, $name, $factory);
        }

        foreach ($config['dependencies']['factories'] as $name => $factory) {
            $this->assertContainerHasService($container, $name, $factory);
        }
    }

    /**
     * @param string|array $factory
     */
    private function assertContainerHasService(ContainerInterface $container, string $name, $factory): void
    {
        if (is_array($factory)) {
            $factory = $factory[0];
        }

        $this->assertTrue($container->has($name), sprintf('Container does not contain service %s', $name));
        $this->assertIsObject(
            $container->get($name),
            sprintf('Cannot get service %s from container using factory %s', $name, $factory)
        );
    }
}
