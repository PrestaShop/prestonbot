<?php

namespace AppBundle\Command;

use AppBundle\Mailer\Mailer;
use AppBundle\PullRequests\Reporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generate report of repository activity to a group a defined users
 * and send it by email.
 */
class SendPullRequestReportCommand extends Command
{
    const DEFAULT_BRANCH = 'develop';

    protected static $defaultName = 'pull_request:report:send_mail';

    /**
     * @var Reporter
     */
    private $reporter;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var array
     */
    private $recipients;

    /**
     * @var string
     */
    private $adminMail;

    public function __construct(Reporter $reporter, Mailer $mailer, array $recipients, string $adminMail)
    {
        parent::__construct();

        $this->reporter = $reporter;
        $this->mailer = $mailer;
        $this->recipients = $recipients;
        $this->adminMail = $adminMail;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Send pull requests by tag report mails.')
            ->addArgument(
                'branch',
                InputArgument::OPTIONAL,
                'Select branch (`develop` by default)'
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
        $branch = $input->getArgument('branch') ? $input->getArgument('branch') : self::DEFAULT_BRANCH;
        $nbMails = 0;

        $io->title('Pull requests Reporter');
        $io->comment('List of recipients');
        $headers = ['Group', 'Emails'];
        $io->table($headers, $this->getRows($this->recipients));

        try {
            foreach ($this->recipients as $groupName => $groupMembers) {
                foreach ($groupMembers as $groupMember) {
                    $nbMails += $this->mailer->send(
                        'Daily report '.date('d/m/Y'),
                        $this->adminMail,
                        $groupMember,
                        'mail/pr_sumup_for_mail.html.twig',
                        $this->reporter->reportActivity($branch)
                    );
                }
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }

        $io->success($nbMails.' mails successfuly sent !');
    }

    /**
     * @param $groups
     *
     * @return array
     */
    private function getRows($groups)
    {
        $rows = [];

        foreach ($groups as $groupName => $groupMembers) {
            foreach ($groupMembers as $groupMember) {
                $rows[] = [$groupName, $groupMember];
            }
        }

        return $rows;
    }
}
