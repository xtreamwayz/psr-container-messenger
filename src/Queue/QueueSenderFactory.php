<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Queue;

use DomainException;
use Interop\Queue\PsrContext;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;
use function array_key_exists;
use function sprintf;

class QueueSenderFactory
{
    /** @var string */
    private $queueName;

    public function __construct(?string $queueName = null)
    {
        $this->queueName = $queueName ?? 'messenger.queue.default';
    }

    public function __invoke(ContainerInterface $container) : SenderInterface
    {
        return new QueueSender(
            $container->get(EncoderInterface::class),
            $container->get(PsrContext::class),
            $this->queueName
        );
    }

    /**
     * Creates a new instance from a specified config
     *
     * <code>
     * <?php
     * return [
     *     // '< service alias >'   => [Messenger\Queue\QueueReceiverFactory::class, '< queue name >'],
     *     'queue.default.receiver' => [Messenger\Queue\QueueReceiverFactory::class, 'queue.default'],
     * ];
     * </code>
     *
     * @throws DomainException
     */
    public static function __callStatic(string $queueName, array $arguments) : SenderInterface
    {
        if (! array_key_exists(0, $arguments) || ! $arguments[0] instanceof ContainerInterface) {
            throw new DomainException(sprintf('The first argument must be of type %s', ContainerInterface::class));
        }

        return (new self($queueName))($arguments[0]);
    }
}
