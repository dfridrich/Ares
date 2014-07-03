<?php

namespace Defr\Ares;

/**
 * Class TaxRecord
 * @package Defr\Ares
 * @author Dennis Fridrich <fridrich.dennis@gmail.com>
 */
class TaxRecord
{

    public $taxId = null;

    /**
     * @param $taxId
     */
    public function setTaxId($taxId)
    {
        $this->taxId = !empty($taxId) ? $taxId : null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return strval($this->taxId);
    }

}