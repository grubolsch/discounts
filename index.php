<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use App\Discount\DiscountBulkPerCategoryCheapestItem;
use App\Discount\DiscountBulkPerCategoryFreeItem;
use App\Discount\DiscountCustomerGlobal;
use App\Importer\CustomerImporter;
use App\Importer\OrderImporter;
use App\Importer\ProductImporter;

use App\Model\Category;
use App\Model\PriceCalculator;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;

header('content-type: text/json');

$moneyFormatter = new IntlMoneyFormatter(new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL), new ISOCurrencies());

try {
    if(!isset($_GET['order'])) {
        throw new DomainException('Send a valid order id as GET param, example: index.php?order=1');
    }

    $categories = [
        1 => new Category(1, 'Tools'),
        2 => new Category(2, 'Switches'),
    ];

    $customerImporter = new CustomerImporter();
    $customers = $customerImporter->import();
    $productImporter = new ProductImporter($categories);
    $products = $productImporter->import();

    $orderImporter = new OrderImporter($customers, $products);
    $order = $orderImporter->import('order'. (int)$_GET['order'] .'.json');

    $calculator = new PriceCalculator($order, $moneyFormatter);
    $calculator->addDiscount(new DiscountCustomerGlobal(1000, 10));
    $calculator->addDiscount(new DiscountBulkPerCategoryFreeItem($categories[2], 5));
    $calculator->addDiscount(new DiscountBulkPerCategoryCheapestItem($categories[1], 2, 20));
    $calculator->applyDiscounts();

    echo json_encode($order, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
}
catch(\Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
}