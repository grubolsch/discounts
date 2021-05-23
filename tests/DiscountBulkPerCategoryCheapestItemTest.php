<?php
declare(strict_types=1);

use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use PHPUnit\Framework\TestCase;
use App\Discount\DiscountBulkPerCategoryCheapestItem;
use App\Model\Category;
use App\Model\Customer;
use App\Model\Order;
use App\Model\OrderLine;
use App\Model\PriceCalculator;
use App\Model\Product;
use Money\Money;

// * @description Solves problem: If you buy two or more products of category "Tools" (id 1), you get a 20% discount on the cheapest product.
class DiscountBulkPerCategoryCheapestItemTest extends TestCase
{
    public function testNotEnoughBought()
    {
        $moneyFormatter = new IntlMoneyFormatter(new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL), new ISOCurrencies());

        $category = new Category(1, 'Tools');

        $order = new Order(1, new Customer(1, 'koen', new \DateTimeImmutable, Money::EUR(0)));

        $orderLine = new OrderLine(2, new Product('A1', 'chair', $category, Money::EUR(1000)));
        $order->addOrderLine($orderLine);

        $calculator = new PriceCalculator($order, $moneyFormatter);
        $calculator->addDiscount(new DiscountBulkPerCategoryCheapestItem($category, 10, 20));
        $calculator->applyDiscounts();

        $this->assertEquals(0, $orderLine->getDiscount());
        $this->assertEquals(2000, $orderLine->calculateTotalPrice()->getAmount());
        $this->assertEquals(0, $orderLine->calculateDiscountValue()->getAmount());
    }

    public function testItemsBoughtButWrongCategory()
    {
        $moneyFormatter = new IntlMoneyFormatter(new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL), new ISOCurrencies());

        $category = new Category(1, 'Tools');
        $wrongCategory = new Category(2, 'Wrong category');

        $order = new Order(1, new Customer(1, 'koen', new \DateTimeImmutable, Money::EUR(0)));

        $orderLine = new OrderLine(2, new Product('A1', 'chair', $wrongCategory, Money::EUR(1000)));
        $order->addOrderLine($orderLine);

        $calculator = new PriceCalculator($order, $moneyFormatter);
        $calculator->addDiscount(new DiscountBulkPerCategoryCheapestItem($category, 2, 20));
        $calculator->applyDiscounts();

        $this->assertEquals(0, $orderLine->getDiscount());
        $this->assertEquals(2000, $orderLine->calculateTotalPrice()->getAmount());
        $this->assertEquals(0, $orderLine->calculateDiscountValue()->getAmount());
    }

    public function testEnoughBoughtSingleProduct()
    {
        $moneyFormatter = new IntlMoneyFormatter(new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL), new ISOCurrencies());

        $category = new Category(1, 'Tools');

        $order = new Order(1, new Customer(1, 'koen', new \DateTimeImmutable, Money::EUR(0)));

        $orderLine = new OrderLine(2, new Product('A1', 'chair', $category, Money::EUR(1000)));
        $order->addOrderLine($orderLine);

        $calculator = new PriceCalculator($order, $moneyFormatter);
        $calculator->addDiscount(new DiscountBulkPerCategoryCheapestItem($category, 2, 20));
        $calculator->applyDiscounts();

        $this->assertEquals(20, $orderLine->getDiscount());
        $this->assertEquals(1600, $orderLine->calculateTotalPrice()->getAmount());
        $this->assertEquals(400, $orderLine->calculateDiscountValue()->getAmount());
    }

    public function testEnoughBoughtMultipleProduct()
    {
        $moneyFormatter = new IntlMoneyFormatter(new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL), new ISOCurrencies());

        $category = new Category(1, 'Tools');

        $order = new Order(1, new Customer(1, 'koen', new \DateTimeImmutable, Money::EUR(0)));

        $orderLine = new OrderLine(2, new Product('A1', 'chair', $category, Money::EUR(1000)));
        $orderLine2 = new OrderLine(2, new Product('A2', 'Cheap chair', $category, Money::EUR(500)));
        $order->addOrderLine($orderLine);
        $order->addOrderLine($orderLine2);

        $calculator = new PriceCalculator($order, $moneyFormatter);
        $calculator->addDiscount(new DiscountBulkPerCategoryCheapestItem($category, 2, 20));
        $calculator->applyDiscounts();

        $this->assertEquals(0, $orderLine->getDiscount());
        $this->assertEquals(2000, $orderLine->calculateTotalPrice()->getAmount());
        $this->assertEquals(0, $orderLine->calculateDiscountValue()->getAmount());

        $this->assertEquals(20, $orderLine2->getDiscount());
        $this->assertEquals(800, $orderLine2->calculateTotalPrice()->getAmount());
        $this->assertEquals(200, $orderLine2->calculateDiscountValue()->getAmount());
    }
}