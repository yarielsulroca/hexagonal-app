<?php
namespace App\Domain\Events;

class UserRegistered
{
    public function __construct(
        public int $id,
        public string $email
    ) {}
    //Getters
    public function userId(): int { return $this->id; }
    public function email(): string { return $this->email; }
    public function occurredOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
    //Serialize
    public function serialize(): array
    {
        return [
            'userId' => $this->id,
            'email' => $this->email,
            'occurredOn' => $this->occurredOn()->format('Y-m-d H:i:s')
        ];
    }
}