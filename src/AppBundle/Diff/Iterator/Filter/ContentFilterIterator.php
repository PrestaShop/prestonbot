<?php

namespace AppBundle\Diff\Iterator\Filter;

use Iterator;

class ContentFilterIterator extends \FilterIterator
{
    private $matchRegexps;

    public function __construct(Iterator $iterator, $regexps)
    {
        parent::__construct($iterator);
        $this->matchRegexps = $regexps;
    }

    public function accept()
    {
        $file = $this->getInnerIterator()->current();

        foreach ($this->matchRegexps as $regexp) {
            if (preg_match($regexp, $file->content())) {
                return true;
            }
        }

        return false;
    }
}
