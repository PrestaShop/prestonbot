<?php

namespace AppBundle\Diff\Iterator\Filter;

use Iterator;

class PathFilterIterator extends \FilterIterator
{
    private $matchRegexps;

    public function __construct(Iterator $iterator, $matchRegexps)
    {
        parent::__construct($iterator);
        $this->matchRegexps = $matchRegexps;
    }

    public function accept()
    {
        $file = $this->getInnerIterator()->current();

        foreach ($this->matchRegexps as $regexp) {
            if (preg_match($regexp, $file->name())) {
                return true;
            }
        }

        return false;
    }
}
