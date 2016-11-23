<?php

namespace AppBundle\Diff;

/**
 * File structure from git diff.
 */
class File
{
    /**
     * @var array
     */
    private $lines;

    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    /**
     * @return string
     */
    public function name()
    {
        $fileLine = $this->lines()[0];

        return $fileLine->getFilepath();
    }

    /**
     * @return string
     */
    public function content()
    {
        $content = '';
        foreach ($this->lines as $line) {
            $content .= $line->getContent().PHP_EOL;
        }

        return $content;
    }

    /**
     * @return array
     */
    public function lines()
    {
        return $this->lines;
    }

    /**
     * @param array $lines
     *
     * @return $this
     */
    public function setLines(array $lines)
    {
        $this->lines = $lines;

        return $this;
    }

    /**
     * @return array
     */
    public function additions()
    {
        return array_filter($this->lines, function (Line $line) {
            return $line->isAddition();
        });
    }

    /**
     * @return array
     */
    public function deletions()
    {
        return array_filter($this->lines, function (Line $line) {
            return $line->isDeletion();
        });
    }

    /**
     * @param $regexp
     *
     * @return bool
     */
    public function match($regexp)
    {
        return 1 === preg_match($regexp, $this->content);
    }
}
