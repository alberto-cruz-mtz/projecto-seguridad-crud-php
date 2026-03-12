<?php

namespace Tito\App\Entity;

class Role
{
    private int $id;
    private RoleName $name;
    private string $description;

    public function __construct(int $id, RoleName $name, string $description)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): RoleName
    {
        return $this->name;
    }

    public function setName(RoleName $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

}