<?php
namespace App\Domain\ValueObjects;
class Price{
    public function __construct(private float $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('El precio debe ser mayor a 0');
        }
    }
    public function value(): float { return $this->value; }

    public function __toString(): string { return (string) $this->value; }

    public function equals(Price $other): bool { return $this->value === $other->value; }
    public function add(Price $other): Price { return new Price($this->value + $other->value); }
    public function subtract(Price $other): Price { return new Price($this->value - $other->value); }
    public function multiply(float $factor): Price { return new Price($this->value * $factor); }
    public function divide(float $divisor): Price { return new Price($this->value / $divisor); }
    public function isGreaterThan(Price $other): bool { return $this->value > $other->value; }
    public function isLessThan(Price $other): bool { return $this->value < $other->value; }
    public function isGreaterThanOrEqualTo(Price $other): bool { return $this->value >= $other->value; }
    public function isLessThanOrEqualTo(Price $other): bool { return $this->value <= $other->value; }
}