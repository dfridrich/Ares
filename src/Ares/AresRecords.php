<?php

namespace Defr\Ares;

/**
 * Class AresRecords
 * @package Defr\Ares
 * @author Dennis Fridrich <fridrich.dennis@gmail.com>
 */
class AresRecords implements \ArrayAccess, \IteratorAggregate
{

    public $array = Array();

    function offsetExists($offset)
    {
        if (isset($this->array[$offset])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param mixed $offset
     * @return bool|AresRecord
     */
    function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->array[$offset];
        } else {
            return false;
        }
    }

    function offsetSet($offset, $value)
    {
        if ($offset) {
            $this->array[$offset] = $value;
        } else {
            $this->array[] = $value;
        }
    }

    function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    function getIterator()
    {
        return new \ArrayIterator($this->array);
    }

}