<?php

namespace Defr\Ares;

/**
 * Class AresRecords.
 *
 * @author Dennis Fridrich <fridrich.dennis@gmail.com>
 */
final class AresRecords implements \ArrayAccess, \IteratorAggregate
{
    public $array = [];

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

    public function offsetSet($offset, $value)
    {
        if ($offset) {
            $this->array[$offset] = $value;
        } else {
            $this->array[] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->array);
    }
}
