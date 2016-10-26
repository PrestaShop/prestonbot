<?php

namespace AppBundle\Diff;

/**
 * File structure from git diff.
 */
class File
{
    private $lines;

    public function __construct($lines)
    {
        $this->lines = $lines;
    }

    public function name()
    {
        $fileLine = $this->lines()[0];

        return $fileLine->getFilepath();
    }

    public function content()
    {
        $content = '';
        foreach ($this->lines as $line) {
            $content .= $line->getContent().PHP_EOL;
        }

        return $content;
    }

    public function lines()
    {
        return $this->lines;
    }

    public function setLines($lines)
    {
        $this->lines = $lines;

        return $this;
    }

    public function additions()
    {
        return array_filter($this->lines, function ($line) {
            return $line->isAddition();
        });
    }

    public function deletions()
    {
        return array_filter($this->lines, function ($line) {
            return $line->isDeletion();
        });
    }

    public function match($regexp)
    {
        return 1 === preg_match($regexp, $this->content);
    }
}
