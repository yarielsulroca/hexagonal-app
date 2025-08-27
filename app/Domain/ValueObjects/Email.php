<?php

namespace App\Domain\ValueObjects;

class Email{
    public function __construct(private string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido');
        }
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    
    // Método para comparar emails
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}