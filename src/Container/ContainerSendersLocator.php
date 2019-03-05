<?php

declare(strict_types=1);

namespace Xtreamwayz\Expressive\Messenger\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;

use function class_implements;
use function class_parents;
use function get_class;
use function in_array;
use function is_string;

class ContainerSendersLocator implements SendersLocatorInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var array */
    private $senders;

    /** @var array */
    private $sendAndHandle;

    public function __construct(ContainerInterface $container, array $senders, array $sendAndHandle = [])
    {
        $this->container     = $container;
        $this->senders       = $senders;
        $this->sendAndHandle = $sendAndHandle;
    }

    /**
     * {@inheritdoc}
     */
    public function getSenders(Envelope $envelope, ?bool &$handle = false) : iterable
    {
        $handle = false;
        $sender = null;
        $seen   = [];

        foreach (self::listTypes($envelope) as $type) {
            $senders = $this->senders[$type] ?? [];

            if (is_string($senders)) {
                $senders = [$senders];
            }

            foreach ($senders as $alias => $sender) {
                if (! in_array($sender, $seen, true)) {
                    yield $seen[] = $this->container->get($sender);
                }
            }
            $handle = $handle ?: $this->sendAndHandle[$type] ?? false;
        }

        $handle = $handle || $sender === null;
    }

    private static function listTypes(Envelope $envelope) : array
    {
        $class = get_class($envelope->getMessage());

        return [$class => $class]
            + [$class . 'Sender' => $class . 'Sender']
            + class_parents($class)
            + class_implements($class)
            + ['*' => '*'];
    }
}
