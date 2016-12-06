<?php

namespace Mindy\Component\Template\Helper;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Traversable;

/**
 * Class ContextIterator
 * @package Mindy\Component\Template
 */
class ContextIterator implements Iterator
{
    protected $sequence;

    /**
     * @var int twig compatibility
     */
    public $index;
    /**
     * @var int twig compatibility
     */
    public $index0;
    /**
     * @var int twig compatibility
     */
    public $revindex;
    /**
     * @var int twig compatibility
     */
    public $revindex0;

    /**
     * @var int django compatibility
     */
    public $counter;
    /**
     * @var int django compatibility
     */
    public $counter0;
    /**
     * @var int django compatibility
     */
    public $revcounter;
    /**
     * @var int django compatibility
     */
    public $revcounter0;

    public function __construct($sequence, $parent)
    {
        if ($sequence instanceof IteratorAggregate) {
            $this->length = ($sequence instanceof Countable) ? count($sequence) : iterator_count($sequence);
            $this->sequence = $sequence->getIterator();
        } elseif ($sequence instanceof Traversable) {
            $this->length = ($sequence instanceof Countable) ? count($sequence) : iterator_count($sequence);
            $this->sequence = $sequence;
        } elseif (is_array($sequence)) {
            $this->length = count($sequence);
            $this->sequence = new ArrayIterator($sequence);
        } else {
            $this->length = 0;
            $this->sequence = new ArrayIterator;
        }
        $this->parent = $parent;
    }

    public function rewind()
    {
        $this->sequence->rewind();

        $this->index0 = $this->counter0 = 0;
        $this->counter = $this->index = $this->index0 + 1;
        $this->first = $this->counter == 1;
        $this->last = $this->counter == $this->length;

        $this->revcounter0 = $this->revindex0 = $this->length - 1;
        $this->revcounter = $this->revindex = $this->length;
    }

    public function key()
    {
        return $this->sequence->key();
    }

    public function valid()
    {
        return $this->sequence->valid();
    }

    public function next()
    {
        $this->sequence->next();

        $this->index0 = $this->counter0 += 1;
        $this->counter = $this->index += 1;
        $this->first = $this->counter == 1;
        $this->last = $this->counter == $this->length;

        $this->revcounter0 = $this->revindex0 -= 1;
        $this->revcounter = $this->revindex -= 1;
    }

    public function current()
    {
        return $this->sequence->current();
    }
}