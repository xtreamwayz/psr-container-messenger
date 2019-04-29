<?php
declare(strict_types = 1);

namespace XtreamwayzTest\Expressive\Messenger\Container;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Xtreamwayz\Expressive\Messenger\Container\ContainerSendersLocator;
use XtreamwayzTest\Expressive\Messenger\Fixtures\DummyEvent;
use Zend\ServiceManager\ServiceManager;

class ContainerSendersLocatorTest extends TestCase
{
    public function testAllowsToSendAndHandleSpecificTypes()
    {
        $container = new ServiceManager([
            'factories' => [
                'foo' => static function () {
                    return 'bar';
                }
            ]
        ]);
        $locator = new ContainerSendersLocator($container, [DummyEvent::class => 'foo'], [DummyEvent::class]);
        $message = new DummyEvent();
        $handle = false;

        $senders = iterator_to_array($locator->getSenders(new Envelope($message), $handle));

        self::assertEquals(['bar'], $senders);
        self::assertTrue($handle);
    }
}
