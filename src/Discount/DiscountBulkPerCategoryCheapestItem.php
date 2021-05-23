<?php
declare(strict_types=1);

namespace App\Discount;

use App\Model\Category;
use App\Model\Order;
use App\Model\OrderLine;
use Money\Formatter\IntlMoneyFormatter;

/*
* @description Solves problem: If you buy two or more products of category "Tools" (id 1), you get a 20% discount on the cheapest product.
 * */
class DiscountBulkPerCategoryCheapestItem implements DiscountInterface
{
    private Category $category;
    private int $threshold;
    private float $discount;

    public function __construct(Category $category, int $threshold, float $discount)
    {
        if($threshold <= 0) {
            throw new \InvalidArgumentException('Threshold needs to be above 0');
        }

        if($discount <= 0) {
            throw new \InvalidArgumentException('Discount needs to be above 0');
        }

        $this->category = $category;
        $this->threshold = $threshold;
        $this->discount = $discount;
    }

    public function apply(Order $order, IntlMoneyFormatter $moneyFormatter): void
    {
        /** @var OrderLine[] $lines */
        $lines = array_filter($order->getLines(), function(OrderLine $orderLine) {
            return $orderLine->getProduct()->getCategory() === $this->category;
        });

        $cheapestLine = null;
        $quantity = 0;
        foreach($lines AS $line) {
            $quantity += $line->getQuantity();

            //find the cheapest product at the some time

            /*
             * @todo: edge case not in documentation, what if 2 products have the same price?
             * I decided to go for giving the most value to the customer, pick the one with the highest quantity.
             * */
            if($cheapestLine === null
                || $line->getProduct()->getPrice()->lessThan($cheapestLine->getProduct()->getPrice())
                || ($line->getProduct()->getPrice()->lessThanOrEqual($cheapestLine->getProduct()->getPrice()) && $line->getQuantity() > $cheapestLine->getQuantity())
            ) {
                $cheapestLine = $line;
            }
        }

        if($quantity < $this->threshold) {//@todo for over meaning ABOVE threshold? Check with customer
            return;
        }

        if($cheapestLine === null) {
            throw new \DomainException('Could not find cheapest product');
        }

        $cheapestLine->setDiscount($this->discount);
        $cheapestLine->addComment(sprintf(
            '%01.2f %% discount (value: %s) because you purchased at least %d products in category %s',
            $cheapestLine->getDiscount(),
            $moneyFormatter->format($cheapestLine->calculateDiscountValue()),
            $this->threshold,
            $this->category->getName())
        );
    }
}

