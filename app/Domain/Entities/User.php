<?php
namespace App\Domain\Entities;

use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use App\Domain\Events\UserRegistered;

class User
{
    private array $events = [];

    public function __construct(
        public int $id,
        public string $name,
        public Email $email,
        public Password $password,
        public bool $isActive = true,
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        private \DateTimeImmutable $updatedAt = new \DateTimeImmutable()
    ) {}
    
    // Getters y métodos de dominio
    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function email(): Email { return $this->email; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    // Método de dominio para registrar usuario
    public function register(): void
    {
        $this->events[] = new UserRegistered($this->id, $this->email->value());
    }

    // Obtener eventos pendientes
    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}