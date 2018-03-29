<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Queue;

use Interop\Queue\PsrContext;
use Symfony\Component\Messenger\Transport\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Throwable;
use XtreamLabs\Expressive\Messenger\Exception\RejectMessageException;
use XtreamLabs\Expressive\Messenger\Exception\RequeueMessageException;

class QueueReceiver implements ReceiverInterface
{
    /** @var DecoderInterface */
    private $decoder;

    /** @var PsrContext */
    private $psrContext;

    /** @var string */
    private $queueName;

    /** @var int */
    private $receiveTimeout;

    public function __construct(DecoderInterface $decoder, PsrContext $psrContext, string $queueName)
    {
        $this->decoder        = $decoder;
        $this->psrContext     = $psrContext;
        $this->queueName      = $queueName;
        $this->receiveTimeout = 1000; // 1s
    }

    public function receive() : iterable
    {
        $queue    = $this->psrContext->createQueue($this->queueName);
        $consumer = $this->psrContext->createConsumer($queue);

        while (true) {
            $message = $consumer->receive($this->receiveTimeout);
            if ($message === null) {
                continue;
            }

            try {
                yield $this->decoder->decode([
                    'body'       => $message->getBody(),
                    'headers'    => $message->getHeaders(),
                    'properties' => $message->getProperties(),
                ]);

                $consumer->acknowledge($message);
            } catch (RejectMessageException $e) {
                $consumer->reject($message);
            } catch (RequeueMessageException $e) {
                $consumer->reject($message, true);
            } catch (Throwable $e) {
                $consumer->reject($message);
            }
        }
    }
}
