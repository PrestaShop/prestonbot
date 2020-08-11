<?php

namespace Tests\AppBundle;

use AppBundle\GithubDownloaderInterface;
use Lpdigital\Github\Entity\PullRequest;

class FakeGithubDownloader implements GithubDownloaderInterface
{
    public function downloadAndExtract(PullRequest $pullRequest, $head = true): string
    {
        $info = true === $head ? $pullRequest->getHead() : $pullRequest->getBase();

        return $pullRequest->getId().'/'.$info['sha'];
    }
}
