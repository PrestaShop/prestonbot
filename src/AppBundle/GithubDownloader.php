<?php

namespace AppBundle;

use AppBundle\Diff\Diff;
use Github\Api\Repository\Contents;
use Lpdigital\Github\Entity\PullRequest;
use ZipArchive;

class GithubDownloader implements GithubDownloaderInterface
{
    private const MAX_SINGLE_FILE = 100; // Max of file downloaded one by one, before downloading the whole repo

    private const GITHUB_RAW_URL = 'https://raw.githubusercontent.com';

    /**
     * @var Contents
     */
    private $contents;

    /**
     * @var string
     */
    private $cacheDir;

    public function __construct(Contents $contents, string $cacheDir)
    {
        $this->contents = $contents;
        $this->cacheDir = $cacheDir;
    }

    public function downloadAndExtract(PullRequest $pullRequest, $head = true) : string
    {
        $info = $head === true ? $pullRequest->getHead() : $pullRequest->getBase();
        $archiveFullPath = $this->cacheDir.'/'.$info['sha'].'.zip';
        $user = $info['user']['login'];
        $repo = $info['repo']['name'];
        $sha = $info['sha'];
        $extractedDirName = $user.'-'.$repo.'-'.substr($sha, 0, 7);
        $extractedDirFullPath = $this->cacheDir.'/'.$extractedDirName;

        if (!file_exists($extractedDirFullPath)) {
            if (false === $head || self::MAX_SINGLE_FILE <= $pullRequest->getChangedFiles()) {
                $archive = $this->contents->archive($user, $repo, 'zipball', $sha);
                file_put_contents($archiveFullPath, $archive);
                $this->extract($archiveFullPath, $this->cacheDir);
            } else {
                $content = file_get_contents($pullRequest->getDiffUrl());
                $diff = Diff::create($content);
                foreach ($diff->getIterator() as $file) {
                    $url = self::GITHUB_RAW_URL.'/'.$user.'/'.$repo.'/'.$sha.'/'.$file->path();
                    $fullPath = $extractedDirFullPath.'/'.$file->path();
                    $dir = dirname($fullPath);
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    file_put_contents($fullPath, file_get_contents($url));
                }
            }
        }

        return $extractedDirName;
    }

    private function extract(string $zipfile, string $destination)
    {
        $zip = new ZipArchive();
        if ($zip->open($zipfile)) {
            $zip->extractTo($destination);
            $zip->close();
        }
    }
}