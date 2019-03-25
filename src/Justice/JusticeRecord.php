<?php

namespace Defr\Justice;

use Defr\ValueObject\Person;

final class JusticeRecord
{
    /**
     * @var array|Person[]
     */
    private $people;

    /**
     * @var bool
     */
    private $insolvencyRecord = false;

    /**
     * @var bool
     */
    private $executionRecord = false;

    public function setPeople(array $people)
    {
        $this->people = $people;
    }

    /**
     * @return array|Person[]
     */
    public function getPeople()
    {
        return $this->people;
    }

    /**
     * @return bool
     */
    public function isInsolvencyRecord()
    {
        return $this->insolvencyRecord;
    }

    /**
     * @param bool $insolvencyRecord
     */
    public function setInsolvencyRecord($insolvencyRecord)
    {
        $this->insolvencyRecord = $insolvencyRecord;
    }

    /**
     * @return bool
     */
    public function isExecutionRecord()
    {
        return $this->executionRecord;
    }

    /**
     * @param bool $executionRecord
     */
    public function setExecutionRecord($executionRecord)
    {
        $this->executionRecord = $executionRecord;
    }

}
