<?php

namespace Defr\Justice;

use Defr\ValueObject\Person;

final class JusticeRecord
{
    /**
     * @var array|Person[]
     */
    private $people;

    public function __construct(array $people)
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
}
