<?php

namespace Defr\ValueObject;

use DateTime;
use DateTimeInterface;

final class Person
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var DateTimeInterface
     */
    private $birthday;

    /**
     * @var string
     */
    private $address;

    /**
     * @param string $name
     * @param DateTimeInterface $birthday
     * @param string $address
     */
    public function __construct($name, DateTimeInterface $birthday, $address)
    {
        $this->name = $name;
        $this->birthday = $birthday;
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
}
