<?php
declare(strict_types=1);

namespace App\Discount;

use App\Model\Order;
use Money\Formatter\IntlMoneyFormatter;

interface DiscountInterface
{
    public function apply(Order $order, IntlMoneyFormatter $moneyFormatter) : void;
}