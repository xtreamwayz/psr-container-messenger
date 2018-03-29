<?php

declare(strict_types=1);

namespace XtreamLabs\Expressive\Messenger\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Enhancers\MaximumCountReceiver;
use Symfony\Component\Messenger\Transport\ReceiverInterface;
use Symfony\Component\Messenger\Worker;
use function sprintf;

class MessengerConsumerCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'messenger:consume';

    /** @var MessageBusInterface  */
    private $bus;

    /** @var ContainerInterface  */
    private $receiverLocator;

    public function __construct(MessageBusInterface $bus, ContainerInterface $receiverLocator)
    {
        parent::__construct();

        $this->bus             = $bus;
        $this->receiverLocator = $receiverLocator;
    }

    protected function configure() : void
    {
        $this
            ->setDefinition([
                new InputArgument('receiver', InputArgument::REQUIRED, 'Name of the receiver'),
                new InputOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit the number of received messages'),
            ])
            ->setDescription('Consumes messages')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command consumes messages and dispatches them to the message bus.

    <info>php %command.full_name% <receiver-name></info>

Use the --limit option to limit the number of messages received:

    <info>php %command.full_name% <receiver-name> --limit=10</info>
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $receiverName = $input->getArgument('receiver');
        if (! $this->receiverLocator->has($receiverName)) {
            throw new \RuntimeException(sprintf('Receiver "%s" does not exist.', $receiverName));
        }

        $receiver = $this->receiverLocator->get($receiverName);
        if (! $receiver instanceof ReceiverInterface) {
            throw new \RuntimeException(sprintf(
                'Receiver "%s" is not a valid message consumer. It must implement the "%s" interface.',
                $receiverName,
                ReceiverInterface::class
            ));
        }

        $limit = $input->getOption('limit');
        if ($limit) {
            $receiver = new MaximumCountReceiver($receiver, $limit);
        }

        $worker = new Worker($receiver, $this->bus);
        $worker->run();
    }
}
