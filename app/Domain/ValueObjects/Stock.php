<?php
namespace App\Domain\ValueObjects;
class Stock{
    public function __construct(private int $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('El stock debe ser mayor o igual a 0');
        }
    }
    public function value(): int { return $this->value; }
    public function __toString(): string { return (string) $this->value; }
    public function equals(Stock $other): bool { return $this->value === $other->value; }
    public function add(Stock $other): Stock { return new Stock($this->value + $other->value); }
    public function subtract(Stock $other): Stock { return new Stock($this->value - $other->value); }
    public function isGreaterThan(Stock $other): bool { return $this->value > $other->value; }
    public function isLessThan(Stock $other): bool { return $this->value < $other->value; }
    public function isGreaterThanOrEqualTo(Stock $other): bool { return $this->value >= $other->value; }
    public function isLessThanOrEqualTo(Stock $other): bool { return $this->value <= $other->value; }
}
