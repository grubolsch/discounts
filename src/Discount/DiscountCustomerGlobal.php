<?php
declare(strict_types=1);

namespace App\Discount;

use App\Model\Order;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

// A customer who has already bought for over € 1000, gets a discount of 10% on the whole order.
class DiscountCustomerGlobal implements DiscountInterface
{
    private Money $threshold;
    private float $discount;

    public function __construct(int $threshold, float $discount)
    {
        if($threshold <= 0) {
            throw new \InvalidArgumentException('Threshold needs to be above 0');
        }

        if($discount <= 0) {
            throw new \InvalidArgumentException('Discount needs to be above 0');
        }

        $this->threshold = Money::EUR($threshold * 100);
        $this->discount = $discount;
    }

    public function apply(Order $order, IntlMoneyFormatter $moneyFormatter): void
    {
        if($order->getCustomer()->getRevenue()->lessThanOrEqual($this->threshold)) {
            return;
        }

        $order->setDiscount($this->discount);
        $order->addMessage(sprintf(
            'You get %01.2f %% discount on the entire order, because you already ordered more then %s with us. Thank you for your loyalty!',
            $this->discount,
            $moneyFormatter->format($this->threshold),
        ));
    }
}