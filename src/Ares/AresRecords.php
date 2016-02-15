<?php

namespace Defr\Ares;

use ArrayIterator;

/**
 * Class AresRecords.
 *
 * @author Dennis Fridrich <fridrich.dennis@gmail.com>
 */
final class AresRecords implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var AresRecord[]
     */
    private $array = [];

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (isset($this->array[$offset])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return bool|AresRecord
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->array[$offset];
        } else {
            return false;
        }
    }

    /**
     * @param mixed $offset
     * @param AresRecord $value
     */
    public function offsetSet($offset, $value)
    {
        if ($offset) {
            $this->array[$offset] = $value;
        } else {
            $this->array[] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->array);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->array);
    }
}
