<?php

namespace tests\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use AppBundle\Command\SendPullRequestReportCommand;

class SendPullRequestReportCommandTest extends WebTestCase
{
    protected function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    public function testExecute()
    {
        $application = new Application(static::$kernel);
        $application->add(new SendPullRequestReportCommand());

        $command = $application->find('pull_request:report:send_mail');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/Pull requests Reporter/', $commandTester->getDisplay());
        $this->assertRegExp('/\/\/ List of recipients/', $commandTester->getDisplay());
        $this->assertRegExp('/waiting for code review/', $commandTester->getDisplay());
        $this->assertRegExp('/waiting for QA feedback/', $commandTester->getDisplay());
        $this->assertRegExp('/waiting for PM feedback/', $commandTester->getDisplay());
    }
}
