<?php

namespace Defr\Ares;

/**
 * Class TaxRecord.
 *
 * @author Dennis Fridrich <fridrich.dennis@gmail.com>
 */
class TaxRecord
{
    /**
     * @var string
     */
    public $taxId = null;

    /**
     * @param string $taxId
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
