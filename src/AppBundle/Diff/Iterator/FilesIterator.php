<?php

namespace AppBundle\Diff\Iterator;

use AppBundle\Diff\Iterator\Filter\ContentFilterIterator;
use AppBundle\Diff\Iterator\Filter\PathFilterIterator;

class FilesIterator implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    private $files;
    /**
     * @var \ArrayIterator
     */
    private $iterator;

    public function __construct(array $files)
    {
        $this->files = $files;
        $this->iterator = new \ArrayIterator($this->files);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * @return int
     */
    public function count()
    {
        return iterator_count($this->iterator);
    }

    /**
     * @param string $regexp
     *
     * @return $this
     */
    public function path(string $regexp)
    {
        $this->iterator = new PathFilterIterator($this->iterator, $regexp);

        return $this;
    }

    /**
     * @param string $regexp
     *
     * @return $this
     */
    public function contains(string $regexp)
    {
        $this->iterator = new ContentFilterIterator($this->iterator, $regexp);

        return $this;
    }

    /**
     * @return $this
     */
    public function additions()
    {
        foreach ($this->iterator as $file) {
            $file->setlines($file->additions());
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function deletions()
    {
        foreach ($this->iterator as $file) {
            $file->setLines($file->deletions());
        }

        return $this;
    }
}
