<?php

declare(strict_types=1);

namespace XtreamwayzTest\Expressive\Messenger;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Xtreamwayz\Expressive\Messenger\ConfigProvider;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use function is_array;
use function sprintf;

class ConfigProviderTest extends TestCase
{
    /** @var ConfigProvider */
    private $provider;

    public function setUp() : void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray() : array
    {
        $config = ($this->provider)();

        self::assertInternalType('array', $config);

        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config) : void
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);
        $this->assertArrayHasKey('factories', $config['dependencies']);
        $this->assertInternalType('array', $config['dependencies']['factories']);

        $this->assertArrayHasKey('messenger', $config);
        $this->assertInternalType('array', $config['messenger']);
        $this->assertArrayHasKey('default_middleware', $config['messenger']);
        $this->assertTrue($config['messenger']['default_middleware']);
        $this->assertArrayHasKey('buses', $config['messenger']);
        $this->assertInternalType('array', $config['messenger']['buses']);
        $this->assertArrayHasKey('messenger.bus.command', $config['messenger']['buses']);
        $this->assertArrayHasKey('messenger.bus.event', $config['messenger']['buses']);
        $this->assertArrayHasKey('messenger.bus.query', $config['messenger']['buses']);

        foreach ($config['messenger']['buses'] as $bus) {
            $this->assertArrayHasKey('handlers', $bus);
            $this->assertInternalType('array', $bus['handlers']);
            $this->assertArrayHasKey('middleware', $bus);
            $this->assertInternalType('array', $bus['middleware']);
            $this->assertArrayHasKey('routes', $bus);
            $this->assertInternalType('array', $bus['routes']);
        }
    }

    public function testServicesDefinedInConfigProvider() : void
    {
        // Get dependencies
        $dependencies = $this->provider->getDependencies();

        // Mock dependencies
        $dependencies['services'][LoggerInterface::class] = $this->prophesize(LoggerInterface::class)->reveal();

        // Build container
        $container = $this->getContainer($dependencies);
        foreach ($dependencies['factories'] as $name => $factory) {
            if (is_array($factory)) {
                $factory = $factory[0];
            }

            self::assertTrue($container->has($name), sprintf('Container does not contain service %s', $name));
            self::assertInternalType(
                'object',
                $container->get($name),
                sprintf('Cannot get service %s from container using factory %s', $name, $factory)
            );
        }
    }

    private function getContainer(array $dependencies) : ServiceManager
    {
        $container = new ServiceManager();
        (new Config($dependencies))->configureServiceManager($container);
        $container->setService('config', ($this->provider)());

        return $container;
    }
}
