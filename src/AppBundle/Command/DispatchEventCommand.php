<?php

namespace AppBundle\Command;

use AppBundle\Event\GitHubEvent;
use InvalidArgumentException;
use PrestaShop\Github\Event\GithubEventInterface;
use PrestaShop\Github\WebhookHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Generate report of repository activity to a group a defined users
 * and send it by email.
 */
class DispatchEventCommand extends Command
{
    private const PATH_ARGUMENT_NAME = 'path';
    private const SUCCESS = 0;
    private const EVENT_NOT_FOUND = 1;

    /**
     * @var WebhookHandler
     */
    private $webhookHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $repositoryOwner;

    /**
     * @var string
     */
    private $repositoryName;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        WebhookHandler $webhookHandler,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        string $repositoryOwner,
        string $repositoryName
    ) {
        parent::__construct();
        $this->webhookHandler = $webhookHandler;
        $this->logger = $logger;
        $this->repositoryOwner = $repositoryOwner;
        $this->repositoryName = $repositoryName;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('prestonbot:event:dispatch')
            ->setDescription('Dispatch the given event as json file argument.')
            ->addArgument(
                self::PATH_ARGUMENT_NAME,
                InputArgument::REQUIRED,
                'The absolute path to the event json file.'
            )
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument(self::PATH_ARGUMENT_NAME);
        $io->title('Dispatch the event contained in '.$path);

        $eventAsArray = json_decode(file_get_contents($path), true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('Invalid JSON body');
        }
        $event = $this->createGithubEventFromEvent($eventAsArray);

        if (null === $event) {
            $io->error('[err] event not found.');

            return self::EVENT_NOT_FOUND;
        }

        $eventName = strtolower($event->getName()).'_'.$event->getEvent()->getAction();

        $this->logger->info(sprintf('[Event] %s (%s) received',
            $event->getName(),
            $event->getEvent()->getAction()
        ));

        $this->eventDispatcher->dispatch($eventName, $event);

        return self::SUCCESS;
    }

    private function createGithubEventFromEvent(array $event): ?GitHubEvent
    {
        $event = $this->webhookHandler->handle($event);
        if (null === $event || null === $event->getAction() || !$this->isValid($event)) {
            $this->logger->error(
                sprintf(
                    '[Event] %s received from `%s` repository',
                    $event::name(),
                    $event->getRepository()->getFullName()
                )
            );

            return null;
        }

        return new GitHubEvent($event::name(), $event);
    }

    private function isValid(GithubEventInterface $event): bool
    {
        [$repositoryUsername, $repositoryName] = explode('/', $event->getRepository()->getFullName());

        return $repositoryUsername === $this->repositoryOwner && $repositoryName === $this->repositoryName;
    }
}
