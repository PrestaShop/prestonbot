<?php

namespace AppBundle;

use Lpdigital\Github\Entity\PullRequest;

interface GithubDownloaderInterface
{
    public function downloadAndExtract(PullRequest $pullRequest, $head = true): string;
}
