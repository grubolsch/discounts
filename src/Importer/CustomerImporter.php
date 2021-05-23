<?php
declare(strict_types=1);

namespace App\Importer;

use App\Model\Customer;
use Money\Money;

class CustomerImporter
{
    const IMPORT_FILE = 'json/customers.json';

    /**
     * @return Customer[]
     * @throws \JsonException
     */
    public function import() : array
    {
        $rawCustomers = json_decode(file_get_contents(self::IMPORT_FILE), true, 512, JSON_THROW_ON_ERROR);

        $customers = [];
        foreach($rawCustomers AS $rawCustomer) {
            $customers[$rawCustomer['id']] = new Customer(
                (int)$rawCustomer['id'],
                $rawCustomer['name'],
                \DateTime::createFromFormat('Y-m-j', $rawCustomer['since']),
                Money::EUR((int)($rawCustomer['revenue']*100))
            );
        }

        return $customers;
    }
}