<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 
 */
class SendPullRequestReportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pull_request:report:send_mail')
            ->setDescription('Send pull requests by tag report mails.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
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
                        $reporter->reportActivity()
                    );
                }
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }

        $io->success($nbMails.' mails successfuly sent !');
    }

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
