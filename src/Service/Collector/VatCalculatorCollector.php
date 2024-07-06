<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Service\Collector;

use App\Entity\PurchaseItem;
use App\Service\Contract\VatCalculatorInterface;

class VatCalculatorCollector
{
    /**
     * @var VatCalculatorInterface[]
     */
    private array $items;

    public function __construct(iterable $vatCalculator)
    {
        foreach ($vatCalculator as $item) {
            $this->addCalculator($item);
        }
    }


    public function addCalculator(VatCalculatorInterface $vatCalculator): void
    {
        $this->items[] = $vatCalculator;
    }

    public function calculate(float $price, PurchaseItem $purchaseItem): float
    {
        foreach ($this->items as $item) {
             $price = $item->calculate($price, $purchaseItem);
        }

        return $price;
    }
}