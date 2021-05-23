<?php
declare(strict_types=1);

namespace App\Discount;

use App\Model\Category;
use App\Model\Order;
use App\Model\OrderLine;
use Money\Formatter\IntlMoneyFormatter;

/**
 * @description For every product of category "Switches" (id 2), when you buy five, you get a sixth for free.
 * @todo question for client: EVERY 5 items? Or only once
 * For example, when I buy 10, do I get 2 items for free?
 */
class DiscountBulkPerCategoryFreeItem implements DiscountInterface
{
    private Category $category;
    private int $threshold;
    private int $freeItems;

    public function __construct(Category $category, int $threshold, int $freeItems=1)
    {
        $this->category = $category;
        $this->threshold = $threshold;
        $this->freeItems = $freeItems;
    }

    public function apply(Order $order, IntlMoneyFormatter $moneyFormatter): void
    {
        /** @var OrderLine[] $lines */
        $lines = array_filter($order->getLines(), function(OrderLine $orderLine) {
            return $orderLine->getProduct()->getCategory() === $this->category && $orderLine->getQuantity() >= $this->threshold;
        });

        foreach($lines AS $line) {

            $line->setFreeItems((int)floor($line->getQuantity() / $this->threshold) * $this->freeItems);

            $line->addComment(sprintf(
                    '+ %d free items because you ordered more then %d in category %s',
                    $line->getFreeItems(),
                    $this->threshold,
                    $this->category->getName())
            );
        }
    }
}
