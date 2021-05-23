<?php
declare(strict_types=1);

namespace App\Model;

class Product
{
    private string $id;
    private string $description;
    private Category $category;
    private \Money\Money $price;

    public function __construct(string $id, string $description, Category $category, \Money\Money $price)
    {
        $this->id = $id;
        $this->description = $description;
        $this->category = $category;
        $this->price = $price;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getPrice(): \Money\Money
    {
        return $this->price;
    }
}