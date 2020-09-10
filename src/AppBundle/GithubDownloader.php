<?php

declare(strict_types=1);

namespace AppBundle;

use AppBundle\Diff\Diff;
use Github\Api\Repository\Contents;
use PrestaShop\Github\Entity\PullRequest;
use Symfony\Component\Filesystem\Filesystem;
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

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct(Contents $contents, string $cacheDir)
    {
        $this->contents = $contents;
        $this->cacheDir = $cacheDir;
        $this->fileSystem = new Filesystem();
    }

    public function downloadAndExtract(PullRequest $pullRequest, $head = true): string
    {
        $info = true === $head ? $pullRequest->getHead() : $pullRequest->getBase();

        if (!file_exists($this->getExtractedFullpathDir($info))) {
            if (false === $head || self::MAX_SINGLE_FILE <= $pullRequest->getChangedFiles()) {
                $this->downloadAndExtractArchive($info);
            } else {
                $this->downloadChangedFiles($info, $pullRequest->getDiffUrl());
            }
        }

        return $this->getExtractedDirName($info);
    }

    private function downloadAndExtractArchive(array $info): void
    {
        $archive = $this->contents->archive(
            $info['user']['login'],
            $info['repo']['name'],
            'zipball',
            $info['sha']
        );
        $archiveFullPath = $this->cacheDir.'/'.$info['sha'].'.zip';
        $this->fileSystem->dumpFile($archiveFullPath, $archive);
        $this->extract($archiveFullPath, $this->cacheDir);
    }

    private function downloadChangedFiles(array $info, string $diffUrl): void
    {
        $content = file_get_contents($diffUrl);
        $diff = Diff::create($content);
        foreach ($diff->getIterator() as $file) {
            $url = $this->getDownloadFileUrl($file, $info);
            $fullPath = $this->getExtractedFullpathDir($info).'/'.$file->path();
            if (!$this->fileSystem->exists(\dirname($fullPath))) {
                $this->fileSystem->mkdir(\dirname($fullPath));
            }
            $this->fileSystem->copy($url, $fullPath);
        }
    }

    private function getExtractedDirName($info): string
    {
        return sprintf(
            '%s-%s-%s',
            $info['user']['login'],
            $info['repo']['name'],
            substr($info['sha'], 0, 7)
        );
    }

    private function getExtractedFullpathDir($info): string
    {
        return $this->cacheDir.'/'.$this->getExtractedDirName($info);
    }

    private function getDownloadFileUrl($file, $info)
    {
        return sprintf(
            '%s/%s/%s/%s/%s',
            self::GITHUB_RAW_URL,
            $info['user']['login'],
            $info['repo']['name'],
            $info['sha'],
            $file->path()
        );
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
