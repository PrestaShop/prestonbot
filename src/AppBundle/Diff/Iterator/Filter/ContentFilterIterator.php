<?php

namespace AppBundle\Diff\Iterator\Filter;

use Iterator;

class ContentFilterIterator extends \FilterIterator
{
    private $matchRegexp;

    public function __construct(Iterator $iterator, $regexp)
    {
        parent::__construct($iterator);
        $this->matchRegexp = $regexp;
    }

    public function accept()
    {
        $file = $this->getInnerIterator()->current();

        if (preg_match($this->matchRegexp, $file->content())) {
            return true;
        }

        return false;
    }
}
