<?php

namespace AppBundle\Diff\Iterator;

use AppBundle\Diff\Iterator\Filter\AdditionsFilterIterator;
use AppBundle\Diff\Iterator\Filter\DeletionsFilterIterator;
use AppBundle\Diff\Iterator\Filter\ContentFilterIterator;
use AppBundle\Diff\Iterator\Filter\PathFilterIterator;

class FilesIterator implements \IteratorAggregate, \Countable
{
    private $contains;
    private $files;
    private $iterator;
    private $names;

    public function __construct($files)
    {
        $this->files = $files;
        $this->iterator = new \ArrayIterator($this->files);
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    public function count()
    {
        return iterator_count($this->iterator);
    }

    public function path($regexp)
    {
        $this->names[] = $regexp;
        $this->iterator = new PathFilterIterator($this->iterator, $this->names);

        return $this;
    }

    public function contains($regexp)
    {
        $this->contains[] = $regexp;
        $this->iterator = new ContentFilterIterator($this->iterator, $this->contains);

        return $this;
    }

    public function additions()
    {
        foreach ($this->iterator as &$file) {
            $file->setlines($file->additions());
        }

        return $this;
    }

    public function deletions()
    {
        foreach ($this->iterator as &$file) {
            $file->setLines($file->deletions());
        }

        return $this;
    }
}
