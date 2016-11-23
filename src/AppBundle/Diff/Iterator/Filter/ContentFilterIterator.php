<?php

namespace AppBundle\Diff\Iterator\Filter;

use Iterator;

class ContentFilterIterator extends \FilterIterator
{
    /**
     * @var string
     */
    private $matchRegexp;

    public function __construct(Iterator $iterator, string $regexp)
    {
        parent::__construct($iterator);
        $this->matchRegexp = $regexp;
    }

    /**
     * @return bool
     */
    public function accept()
    {
        $file = $this->getInnerIterator()->current();

        if (preg_match($this->matchRegexp, $file->content())) {
            return true;
        }

        return false;
    }
}
