<?php
declare(strict_types=1);

namespace App\Model;

class Category
{
    const TOOLS_CATEGORY = 1;
    const SWITCHES_CATEGORY = 2;

    private int $id;
    private string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}