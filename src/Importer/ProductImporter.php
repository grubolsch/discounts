<?php
declare(strict_types=1);

namespace App\Importer;

use App\Model\Category;
use App\Model\Product;
use Money\Money;

class ProductImporter
{
    const IMPORT_FILE = 'json/products.json';

    /** @var Category[] */
    private array $categories = [];

    public function __construct(array $categories)
    {
        $this->categories = $categories;
    }


    /** @return Product[]
     * @throws \JsonException
     */
    public function import() : array
    {
        $rawProducts = json_decode(file_get_contents(self::IMPORT_FILE), true, 512, JSON_THROW_ON_ERROR);

        $products = [];
        foreach($rawProducts AS $rawProduct) {
            if(!isset($this->categories[$rawProduct['category']])) {
                throw new \DomainException(sprintf('Category %d not found', $rawProduct['category']));
            }

            $products[$rawProduct['id']] = new Product((string)$rawProduct['id'], $rawProduct['description'], $this->categories[$rawProduct['category']], Money::EUR((int)($rawProduct['price']*100)));
        }
        return $products;
    }
}