<?php
declare(strict_types=1);

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

class DiscountCustomerGlobalTest extends \PHPUnit\Framework\TestCase
{
    public function testCustomerBoughtNotEnoughTest()
    {
        $moneyFormatter = new IntlMoneyFormatter(new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL), new ISOCurrencies());

        $category = new Category(1, 'Tools');

        $order = new Order(1, new Customer(1, 'koen', new \DateTimeImmutable, Money::EUR(900)));

        $orderLine = new OrderLine(1, new Product('A1', 'chair', $category, Money::EUR(1000)));
        $order->addOrderLine($orderLine);

        $calculator = new PriceCalculator($order, $moneyFormatter);
        $calculator->addDiscount(new DiscountCustomerGlobal(1000, 10));
        $calculator->applyDiscounts();

        $this->assertEquals(0, $order->getDiscount());
        $this->assertEquals(1000, $order->calculateTotalPrice()->getAmount());
        $this->assertEquals(0, $order->calculateDiscountValue()->getAmount());
    }

    public function testCustomerBoughtEnoughTest()
    {
        $moneyFormatter = new IntlMoneyFormatter(new \NumberFormatter('nl_NL', \NumberFormatter::DECIMAL), new ISOCurrencies());

        $category = new Category(1, 'Tools');

        $order = new Order(1, new Customer(1, 'koen', new \DateTimeImmutable, Money::EUR(100100)));

        $orderLine = new OrderLine(1, new Product('A1', 'chair', $category, Money::EUR(1000)));
        $order->addOrderLine($orderLine);

        $calculator = new PriceCalculator($order, $moneyFormatter);
        $calculator->addDiscount(new DiscountCustomerGlobal(1000, 10));
        $calculator->applyDiscounts();

        $this->assertEquals(10, $order->getDiscount());
        $this->assertEquals(900, $order->calculateTotalPrice()->getAmount());
        $this->assertEquals(100, $order->calculateDiscountValue()->getAmount());
    }
}