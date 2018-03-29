<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Queue;

use Interop\Queue\PsrContext;
use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;

class QueueSender implements SenderInterface
{
    /** @var EncoderInterface */
    private $encoder;

    /** @var PsrContext */
    private $psrContext;

    /** @var string */
    private $queueName;

    public function __construct(EncoderInterface $encoder, PsrContext $psrContext, string $queueName)
    {
        $this->encoder    = $encoder;
        $this->psrContext = $psrContext;
        $this->queueName  = $queueName;
    }

    /**
     * @param object $message
     */
    public function send($message) : void
    {
        $encodedMessage = $this->encoder->encode($message);
        $psrMessage     = $this->psrContext->createMessage(
            $encodedMessage['body'],
            $encodedMessage['properties'] ?? [],
            $encodedMessage['headers'] ?? []
        );

        $queue    = $this->psrContext->createQueue($this->queueName);
        $producer = $this->psrContext->createProducer();
        $producer->send($queue, $psrMessage);
    }
}
