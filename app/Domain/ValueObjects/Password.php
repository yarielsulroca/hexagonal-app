<?php
namespace App\Domain\ValueObjects;

class Password{
    public function __construct(private string $value)
    {
        if (strlen($value) < 8) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres');
        }
        if (!preg_match('/[A-Z]/', $value)) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos una letra mayúscula');
        }
        if (!preg_match('/[a-z]/', $value)) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos una letra minúscula');
        }
        if (!preg_match('/[0-9]/', $value)) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos un número');
        }
        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos un carácter especial');
        }
        $this->value = $value;
    }

    public function value(): string { return $this->value; } 
    public function __toString(): string { return $this->value; }

    public function equals(Password $other): bool
    {
        return $this->value === $other->value;
    }
}
