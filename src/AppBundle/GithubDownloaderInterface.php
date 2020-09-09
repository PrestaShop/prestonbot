<?php

declare(strict_types=1);

namespace AppBundle;

use PrestaShop\Github\Entity\PullRequest;

interface GithubDownloaderInterface
{
    public function downloadAndExtract(PullRequest $pullRequest, $head = true): string;
}
