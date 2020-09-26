<?php

declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Test;

use PHPUnit\Framework\TestCase;
use Xtreamwayz\PsrContainerMessenger\ConfigProvider;

class ConfigProviderTest extends TestCase
{
    private ConfigProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray(): array
    {
        $config = ($this->provider)();

        $this->assertNotEmpty($config);

        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config): void
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertIsArray($config['dependencies']);
        $this->assertArrayHasKey('factories', $config['dependencies']);
        $this->assertIsArray($config['dependencies']['factories']);

        $this->assertArrayHasKey('messenger', $config);
        $this->assertIsArray($config['messenger']);
        $this->assertArrayHasKey('buses', $config['messenger']);
        $this->assertIsArray($config['messenger']['buses']);
        $this->assertArrayHasKey('messenger.command.bus', $config['messenger']['buses']);
        $this->assertArrayHasKey('messenger.event.bus', $config['messenger']['buses']);
        $this->assertArrayHasKey('messenger.query.bus', $config['messenger']['buses']);

        foreach ($config['messenger']['buses'] as $bus) {
            $this->assertArrayHasKey('handlers', $bus);
            $this->assertIsArray($bus['handlers']);
            $this->assertArrayHasKey('middleware', $bus);
            $this->assertIsArray($bus['middleware']);
            $this->assertArrayHasKey('routes', $bus);
            $this->assertIsArray($bus['routes']);
        }
    }
}
