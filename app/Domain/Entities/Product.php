<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\Stock;
use App\Domain\Events\ProductCreated;
use App\Domain\Events\ProductUpdated;

class Product
{
    private array $events = [];

    public function __construct(
        private ?int $id,
        private string $name,
        private string $description,
        private Price $price,
        private Stock $stock,
        private string $image,
        private int $userId,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {}

    // Getters y métodos de dominio
    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function price(): Price { return $this->price; }
    public function stock(): Stock { return $this->stock; }
    public function userId(): int { return $this->userId; }

    // Método de dominio para actualizar precio
    public function updatePrice(Price $newPrice): void
    {
        $this->price = $newPrice;
        $this->events[] = new ProductUpdated($this->id, 'price', $newPrice->value());
    }

    // Método de dominio para actualizar stock
    public function updateStock(Stock $newStock): void
    {
        $this->stock = $newStock;
        $this->events[] = new ProductUpdated($this->id, 'stock', $newStock->value());
    }

    // Obtener eventos pendientes
    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}