<?php

namespace AppBundle\Diff;

use AppBundle\Diff\Iterator\FilesIterator;
use IteratorAggregate;

/**
 * Extract human readable data from git diff file content.
 */
class Diff implements IteratorAggregate
{
    /**
     * @var string
     */
    private $diffContent;
    /**
     * @var array
     */
    private $files = [];
    /**
     * @var array
     */
    private $lines = [];

    public function __construct(string $diffContent)
    {
        $this->diffContent = $diffContent;
        $this->iterator = $this->buildIterator();
    }

    /**
     * @param string $content
     *
     * @return static
     */
    public static function create(string $content)
    {
        return new static($content);
    }

    /**
     * @return FilesIterator
     */
    public function buildIterator()
    {
        $token = strtok($this->diffContent, PHP_EOL);

        while (false !== $token && null !== $token) {
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

    /**
     * @return FilesIterator
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * @param $regexp
     *
     * @return $this
     */
    public function path($regexp)
    {
        $this->iterator->path($regexp);

        return $this;
    }

    /**
     * @param $regexp
     *
     * @return $this
     */
    public function contains($regexp)
    {
        $this->iterator->contains($regexp);

        return $this;
    }

    /**
     * @return $this
     */
    public function additions()
    {
        $this->iterator->additions();

        return $this;
    }

    /**
     * @return $this
     */
    public function deletions()
    {
        $this->iterator->deletions();

        return $this;
    }

    /**
     * @return bool
     */
    public function match()
    {
        return $this->iterator->count() > 0;
    }
}
