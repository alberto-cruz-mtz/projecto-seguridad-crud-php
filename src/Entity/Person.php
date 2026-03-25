<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Entity;

final class Person
{
    public function __construct(
        private string $userId,
        private string $firstName,
        private string $lastName,
        private int $age,
        private string $address,
        private string $phoneNumber,
        private string $gender,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    /** @return array<string, int|string> */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'age' => $this->age,
            'address' => $this->address,
            'phone_number' => $this->phoneNumber,
            'gender' => $this->gender,
        ];
    }
}
