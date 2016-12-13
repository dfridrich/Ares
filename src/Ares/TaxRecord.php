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
    private $taxId = null;

    /**
     * TaxRecord constructor.
     *
     * @param string $taxId
     */
    public function __construct($taxId)
    {
        $this->taxId = !empty($taxId) ? $taxId : null;
    }

    /**
     * @return string
     */
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return strval($this->taxId);
    }
}
