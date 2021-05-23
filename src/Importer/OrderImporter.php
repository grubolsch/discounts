<?php
declare(strict_types=1);

namespace App\Importer;

use App\Model\Customer;
use App\Model\Order;
use App\Model\OrderLine;
use App\Model\Product;

class OrderImporter
{
    private const DIR = 'json/orders/';

    /** @var Customer[] */
    private array $customers;
    /** @var Product[] */
    private array $products;

    public function __construct(array $customers, array $products)
    {
        $this->customers = $customers;
        $this->products = $products;
    }

    /** @return Order
     * @throws \JsonException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function import(string $file) : Order
    {
        $file = self::DIR . basename($file);

        if(!is_file($file) || !is_readable($file)) {
            throw new \InvalidArgumentException(sprintf('Cannot read file %s', $file));
        }

        $raw = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

        if(!isset($raw['customer-id'])) {
            throw new \DomainException('Invalid order JSON format');
        }

        if(!isset($this->customers[$raw['customer-id']])) {
            throw new \DomainException(sprintf('Invalid customer id %d', $raw['customer-id']));
        }

        $order = new Order(
            (int)$raw['id'],
            $this->customers[$raw['customer-id']],
        );

        foreach ($raw['items'] AS $item) {
            if(!isset($this->products[$item['product-id']])) {
                throw new \DomainException(sprintf('Invalid product id %s', $item['product-id']));
            }

            $order->addOrderLine(new OrderLine((int)$item['quantity'], $this->products[$item['product-id']]));
        }

        return $order;
    }
}