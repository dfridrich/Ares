<?php

namespace Defr\Justice;

use Defr\ValueObject\Person;

final class JusticeRecord
{
    /**
     * @var Person[]
     */
    private $people;

    public function __construct(array $people)
    {
        $this->people = $people;
    }

    /**
     * @return Person[]
     */
    public function getPeople()
    {
        return $this->people;
    }
}
