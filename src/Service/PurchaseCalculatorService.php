<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Service;

use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Service\Collector\PriceCalculatorCollector;
use App\Service\Collector\VatCalculatorCollector;

class PurchaseCalculatorService
{
    public function __construct(private readonly PriceCalculatorCollector $priceCalculatorCollector, private readonly VatCalculatorCollector $vatCalculatorCollector)
    {
    }

    public function calculateTotalQuantity(Purchase $purchase): int
    {
        $totalQuantity = 0;
        foreach ($purchase->getPurchaseItems() as $item) {
            $totalQuantity += $item->getQuantity();
        }

        return $totalQuantity;
    }

    public function calculatePrice(PurchaseItem $purchaseItem): float
    {
        return $this->priceCalculatorCollector->calculate($purchaseItem);
    }

    public function calculateVat(float $price, PurchaseItem $purchaseItem): float
    {
        return $this->vatCalculatorCollector->calculate($price, $purchaseItem);
    }

    public function calculateTotalVat(Purchase $purchase): float
    {
        $total = 0;
        foreach ($purchase->getPurchaseItems() as $item) {
            $price = $this->calculatePrice($item);
            $total += $this->calculateVat($price, $item);
        }

        return $total;
    }

    public function calculateTotalPrice(Purchase $purchase): float
    {
        $total = 0;
        foreach ($purchase->getPurchaseItems() as $item) {
            $total += $this->calculatePrice($item);
        }

        return $total;
    }


}