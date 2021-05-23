<?php
declare(strict_types=1);

namespace App\Model;

use Money\Money;

class Customer
{
    private int $id;
    private string $name;
    private \DateTimeInterface $since;
    private Money $revenue;

    public function __construct(int $id, string $name, \DateTimeInterface $since, Money $revenue)
    {
        $this->id = $id;
        $this->name = $name;
        $this->since = $since;
        $this->revenue = $revenue;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSince(): \DateTimeInterface
    {
        return $this->since;
    }

    public function getRevenue(): Money
    {
        return $this->revenue;
    }
}