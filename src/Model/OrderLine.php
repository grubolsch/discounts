<?php
declare(strict_types=1);

namespace App\Model;

use Money\Money;

class OrderLine implements \JsonSerializable {
    private int $quantity;
    private Product $product;

    private float $discount = 0;
    private int $freeItems = 0;

    private array $comments = [];

    public function __construct(int $quantity, Product $product)
    {
        $this->quantity = $quantity;
        $this->product = $product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function addQuantity(int $quantity)
    {
        $this->quantity += $quantity;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    public function getFreeItems(): int
    {
        return $this->freeItems;
    }

    public function setFreeItems(int $freeItems): void
    {
        $this->freeItems = $freeItems;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function addComment(string $comment): void
    {
        $this->comments[] = $comment;
    }

    public function calculateTotalPrice() : Money
    {
        return $this->calculateTotalPriceWithoutDiscount()->subtract($this->calculateDiscountValue());
    }

    private function calculateTotalPriceWithoutDiscount(): Money
    {
        return $this->product->getPrice()->multiply((string)$this->quantity);
    }

    public function calculateDiscountValue(): Money
    {
        return $this->calculateTotalPriceWithoutDiscount()->multiply((string)$this->discount)->divide('100');
    }

    public function clearDiscounts()
    {
        $this->discount = 0;
        $this->freeItems = 0;
        $this->comments = [];
    }

    public function jsonSerialize()
    {
        return [
            'product' => $this->getProduct()->getId(),
            'quantity' => $this->getQuantity(),
            'freeItems' => $this->getFreeItems(),
            'discount' => $this->calculateDiscountValue(),
            'totalPrice' => $this->calculateTotalPrice(),
            'comment' => implode("\n", $this->getComments())
        ];    
    }
}