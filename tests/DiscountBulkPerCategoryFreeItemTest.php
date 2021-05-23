<?php
declare(strict_types=1);

use App\Discount\DiscountBulkPerCategoryFreeItem;
use PHPUnit\Framework\TestCase;
use App\Discount\DiscountCustomerGlobal;
use App\Model\Category;
use App\Model\Customer;
use App\Model\Order;
use App\Model\OrderLine;
use App\Model\PriceCalculator;
use App\Model\Product;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

class DiscountBulkPerCategoryFreeItemTest extends TestCase
{
    public function freeItemsProvider(): array
    {
        return [
            [5, 10, 0],
            [5, 5, 1],
            [6, 5, 1],
            [10, 5, 2],
        ];
    }

    /**
     * @dataProvider freeItemsProvider
     */
    public function testFreeItems(int $quantity, int $threshold, $freeItemsExpected)
    {
        $moneyFormatter = new IntlMoneyFormatter(new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL), new ISOCurrencies());

        $category = new Category(1, 'Tools');

        $order = new Order(1, new Customer(1, 'koen', new \DateTimeImmutable, Money::EUR(0)));

        $orderLine = new OrderLine($quantity, new Product('A1', 'chair', $category, Money::EUR(1000)));
        $order->addOrderLine($orderLine);

        $calculator = new PriceCalculator($order, $moneyFormatter);
        $calculator->addDiscount(new DiscountBulkPerCategoryFreeItem($category, $threshold));
        $calculator->applyDiscounts();

        $this->assertEquals(0, $orderLine->getDiscount());
        $this->assertEquals($freeItemsExpected, $orderLine->getFreeItems());
    }
}