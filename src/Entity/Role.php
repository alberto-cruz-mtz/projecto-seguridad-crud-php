<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Entity;

final class Role
{
    public function __construct(
        private int $id,
        private string $name,
        private ?string $description,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /** @return array<string, int|string|null> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
