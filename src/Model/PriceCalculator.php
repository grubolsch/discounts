<?php
declare(strict_types=1);

namespace App\Model;

use App\Discount\DiscountInterface;
use Money\Formatter\IntlMoneyFormatter;

class PriceCalculator
{
    /** @var DiscountInterface[] */
    private array $discounts = [];

    private Order $order;
    private IntlMoneyFormatter $moneyFormatter;

    public function __construct(Order $order, IntlMoneyFormatter $moneyFormatter)
    {
        $this->order = $order;
        $this->moneyFormatter = $moneyFormatter;
    }

    public function addDiscount(DiscountInterface $discount) : void
    {
        $this->discounts[] = $discount;
    }

    public function applyDiscounts() : void
    {
        $this->order->clearDiscounts();
        foreach($this->discounts as $discount) {
            $discount->apply($this->order, $this->moneyFormatter);
        }
    }
}