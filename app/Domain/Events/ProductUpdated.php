<?php
namespace App\Domain\Events;
class ProductUpdated{
    public function __construct(
        public int $id,
        public string $field,
        public mixed $value
    ) {}
    public function id(): int { return $this->id; }
    public function field(): string { return $this->field; }
    public function value(): mixed { return $this->value; }
    public function occurredOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
    public function serialize(): array
    {
        return [
            'id' => $this->id,
            'field' => $this->field,
            'value' => $this->value,
            'occurredOn' => $this->occurredOn()->format('Y-m-d H:i:s')
        ];
    }
}