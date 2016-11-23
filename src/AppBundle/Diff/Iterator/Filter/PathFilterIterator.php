<?php

namespace AppBundle\Diff\Iterator\Filter;

use Iterator;

class PathFilterIterator extends \FilterIterator
{
    /**
     * @var string
     */
    private $matchRegexp;

    public function __construct(Iterator $iterator, string $matchRegexp)
    {
        parent::__construct($iterator);
        $this->matchRegexp = $matchRegexp;
    }

    /**
     * @return bool
     */
    public function accept()
    {
        $file = $this->getInnerIterator()->current();

        if (preg_match($this->matchRegexp, $file->name())) {
            return true;
        }

        return false;
    }
}
