<?php

namespace AppBundle\Diff\Iterator\Filter;

use Iterator;

class PathFilterIterator extends \FilterIterator
{
    private $matchRegexps;

    public function __construct(Iterator $iterator, $matchRegexp)
    {
        parent::__construct($iterator);
        $this->matchRegexp = $matchRegexp;
    }

    public function accept()
    {
        $file = $this->getInnerIterator()->current();

        if (preg_match($this->matchRegexp, $file->name())) {
            return true;
        }

        return false;
    }
}
