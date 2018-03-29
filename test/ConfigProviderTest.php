<?php

declare(strict_types=1);

namespace XtreamLabsTest\Expressive\Messenger;

use PHPUnit\Framework\TestCase;
use XtreamLabs\Expressive\Messenger\ConfigProvider;
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
        $this->assertArrayHasKey('middleware', $config['messenger']);
        $this->assertInternalType('array', $config['messenger']['middleware']);
    }

    public function testServicesDefinedInConfigProvider() : void
    {
        $dependencies = $this->provider->getDependencies();

        //ServerUrlHelper::class           => $this->prophesize(ServerUrlHelper::class)->reveal(),
        //UrlHelper::class                 => $this->prophesize(UrlHelper::class)->reveal(),
        //TemplateRendererInterface::class => $this->prophesize(TemplateRendererInterface::class)->reveal(),
        $dependencies['services'] = [];

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
