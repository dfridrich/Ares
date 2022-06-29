<?php

namespace Defr\ValueObject;

use DateTimeInterface;

final class Person
{
    private string $name;

    private ?DateTimeInterface $birthday;

    private ?string $address;

    private DateTimeInterface $registered;

    private ?DateTimeInterface $deleted;

    private ?string $type;

    public function __construct(
        string $name,
        ?DateTimeInterface $birthday,
        ?string $address,
        DateTimeInterface $registered,
        ?DateTimeInterface $deleted,
        ?string $type
    ) {
        $this->name = $name;
        $this->birthday = $birthday;
        $this->address = $address;
        $this->registered = $registered;
        $this->deleted = $deleted;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getRegistered(): DateTimeInterface
    {
        return $this->registered;
    }

    public function getDeleted(): ?DateTimeInterface
    {
        return $this->deleted;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

}
