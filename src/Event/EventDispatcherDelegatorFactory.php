<?php


declare(strict_types=1);

namespace Xtreamwayz\PsrContainerMessenger\Event;

use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;

class EventDispatcherDelegatorFactory implements DelegatorFactoryInterface
{
    protected const MAX_RETRIES = 3;
    protected const WAIT_IN_SECONDS = 5 * 60;
    protected const WAIT_MULTIPLIER = 4;
    public const FAILED_QUEUE = 'failed';

    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        array $options = null
    ): EventDispatcherInterface {
        $config = $container->get('config');
        $availableRoutes = [];
        foreach ($config['messenger']['buses']['messenger.command.bus']['routes'] as $routes) {
            foreach ($routes as $route) {
                if ($route === self::FAILED_QUEUE) {
                    continue;
                }
                $availableRoutes[$route] = $route;
            }
        }

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $callback();

        $strategieContainer = new ContainerBuilder();
        $failureContainer = new ContainerBuilder();

        $logger = $container->get($config['messenger']['logger']);

        $eventDispatcher->addSubscriber(
            new SendFailedMessageForRetryListener($container, $strategieContainer, $logger)
        );

        foreach ($availableRoutes as $availableRoute) {
            $strategieContainer->set(
                $availableRoute,
                new MultiplierRetryStrategy(self::MAX_RETRIES, self::WAIT_IN_SECONDS * 1000, self::WAIT_MULTIPLIER)
            );
            $failureContainer->set($availableRoute, $container->get(self::FAILED_QUEUE));
        }

        $eventDispatcher->addSubscriber(
            new SendFailedMessageToFailureTransportListener($failureContainer, $logger)
        );
        $eventDispatcher->addSubscriber($container->get(Worker\LogFailedWorkerEventSubscriber::class));
        $eventDispatcher->addSubscriber($container->get(Worker\LogWorkerMessageRuntimeSubscriber::class));
        return $eventDispatcher;
    }

    public function createDelegatorWithName(
        ServiceLocatorInterface $serviceLocator,
        string $name,
        string $requestedName,
        callable $callback
    ): EventDispatcherInterface {
        return ($this)($serviceLocator, $name, $callback);
    }
}
