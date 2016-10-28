<?php

namespace AppBundle\Diff;

use AppBundle\Diff\Iterator\FilesIterator;
use IteratorAggregate;

/**
 * Extract human readable data from git diff file content.
 */
class Diff implements IteratorAggregate
{
    private $diffContent;
    private $files = [];
    private $lines = [];

    public function __construct($diffContent)
    {
        $this->diffContent = $diffContent;
        $this->iterator = $this->buildIterator();
    }

    public static function create($content)
    {
        return new static($content);
    }

    public function buildIterator()
    {
        $token = strtok($this->diffContent, PHP_EOL);

        while ($token !== false && $token !== null) {
            $line = new Line($token);

            switch (true) {
                case $line->isFilename() && 0 === count($this->lines):
                    $this->lines[] = $line;
                break;

                case $line->isFilename() && count($this->lines) > 0:
                    $this->files[] = new File($this->lines);
                    $this->lines = [];
                    $this->lines[] = $line;
                break;

                case !$line->isFilename():
                    $this->lines[] = $line;
                break;
            }

            $token = strtok(PHP_EOL);
        }

        if (count($this->lines) > 0) {
            $this->files[] = new File($this->lines);
            $this->lines = [];
        }

        return new FilesIterator($this->files);
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    public function path($regexp)
    {
        $this->iterator->path($regexp);

        return $this;
    }

    public function contains($regexp)
    {
        $this->iterator->contains($regexp);

        return $this;
    }

    public function additions()
    {
        $this->iterator->additions();

        return $this;
    }

    public function deletions()
    {
        $this->iterator->deletions();

        return $this;
    }

    public function match()
    {
        return $this->iterator->count() > 0;
    }
}
