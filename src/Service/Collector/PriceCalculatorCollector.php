<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Service\Collector;

use App\Entity\PurchaseItem;
use App\Service\Calculator\PriceCalculatorInterface;

class PriceCalculatorCollector
{

    /**
     * @var PriceCalculatorInterface[]
     */
    private array $priceCalculators;

    public function __construct(iterable $priceCalculator)
    {
        foreach ($priceCalculator as $item) {
            $this->addCalculator($item);
        }
    }

    public function addCalculator(PriceCalculatorInterface $priceCalculator): void
    {
        $this->priceCalculators[] = $priceCalculator;
    }

    public function calculate(PurchaseItem $purchaseItem): float
    {
        $price = (float)$purchaseItem->getUnitPrice() * $purchaseItem->getQuantity();

        foreach ($this->priceCalculators as $priceCalculator) {
            $price = $priceCalculator->calculate($price, $purchaseItem);
        }

        return $price;
    }
}
