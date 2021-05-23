<?php
declare(strict_types=1);

namespace App\Model;

use Money\Money;

class Order implements \JsonSerializable
{
    private int $id;

    /** @var OrderLine[] */
    private array $lines = [];

    private float $discount = 0;
    private Customer $customer;

    /** @var String[] */
    private array $comments = [];

    public function __construct(int $id, Customer $customer)
    {
        $this->id = $id;
        $this->customer = $customer;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function getLines() : array
    {
        return $this->lines;
    }

    public function getDiscount(): float|int
    {
        return $this->discount;
    }

    public function setDiscount(float|int $discount): void
    {
        $this->discount = $discount;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function addMessage(string $message) : void
    {
        $this->comments[] = $message;
    }

    public function addOrderLine(OrderLine $orderline) : void
    {
        if(isset($this->lines[$orderline->getProduct()->getId()])) {
            $this->lines[$orderline->getProduct()->getId()]->addQuantity($orderline->getQuantity());
        } else {
            $this->lines[$orderline->getProduct()->getId()] = $orderline;
        }
    }

    public function calculateTotalPrice() : Money
    {
        return $this->calculateTotalPriceWithoutDiscount()->subtract($this->calculateDiscountValue());
    }

    private function calculateTotalPriceWithoutDiscount(): Money
    {
        $price = Money::EUR(0);
        foreach($this->lines AS $line) {
            $price = $price->add($line->calculateTotalPrice());
        }

        return $price;
    }

    public function calculateDiscountValue(): Money
    {
        return $this->calculateTotalPriceWithoutDiscount()->multiply((string)$this->discount)->divide((string)100);
    }

    public function clearDiscounts() : void
    {
        $this->discount = 0;
        $this->comments = [];
        foreach($this->getLines() AS $line) {
            $line->clearDiscounts();
        }
    }

    public function jsonSerialize() : array
    {
        return [
            'id' => $this->id,
            'customer' => $this->customer->getId(),
            'lines' => $this->getLines(),
            'globalDiscount' => $this->calculateDiscountValue(),
            'totalPrice' => $this->calculateTotalPrice(),
            'comments' => implode("\n", $this->comments)
        ];
    }
}