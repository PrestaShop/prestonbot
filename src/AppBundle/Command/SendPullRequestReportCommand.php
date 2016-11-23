<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generate report of repository activity to a group a defined users
 * and send it by email.
 */
class SendPullRequestReportCommand extends ContainerAwareCommand
{
    const DEFAULT_BRANCH = 'develop';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pull_request:report:send_mail')
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

        $groups = $this->getContainer()->getParameter('recipients');
        $reporter = $this->getContainer()->get('app.pull_requests.reporter');
        $mailer = $this->getContainer()->get('app.mailer');
        $nbMails = 0;

        $io->title('Pull requests Reporter');
        $io->comment('List of recipients');
        $headers = ['Group', 'Emails'];
        $io->table($headers, $this->getRows($groups));

        try {
            foreach ($groups as $groupName => $groupMembers) {
                foreach ($groupMembers as $groupMember) {
                    $nbMails += $mailer->send(
                        'Daily report '.date('d/m/Y'),
                        $this->getContainer()->getParameter('admin_mail'),
                        $groupMember,
                        'mail/pr_sumup_for_mail.html.twig',
                        $reporter->reportActivity($branch)
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
