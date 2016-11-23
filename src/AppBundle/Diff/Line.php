<?php

namespace AppBundle\Diff;

/**
 * Line structure from git diff.
 */
class Line
{
    const TOKEN_PLUS = '+';

    const TOKEN_MINUS = '-';

    const TOKEN_FILENAME = 'diff --git a/';

    /**
     * @var string
     */
    private $content;

    public function __construct(string $lineContent)
    {
        $this->content = $lineContent;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function isAddition()
    {
        return 0 === strpos($this->content, self::TOKEN_PLUS);
    }

    /**
     * @return bool
     */
    public function isDeletion()
    {
        return 0 === strpos($this->content, self::TOKEN_MINUS);
    }

    /**
     * @return bool
     */
    public function isFilename()
    {
        return 0 === strpos($this->content, self::TOKEN_FILENAME);
    }

    /**
     * @return string
     */
    public function getFilepath()
    {
        if ($this->isFilename()) {
            return basename(substr($this->content, strlen(self::TOKEN_FILENAME)));
        }
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
