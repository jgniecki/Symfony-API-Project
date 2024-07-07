<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Service\Calculator;

use App\Entity\PurchaseItem;

class VatCalculator implements VatCalculatorInterface
{
    public const VAT = 0.23;

    /**
     *  Drugi parametr przez charakterystyke zadania jest nie potrzebny z racji założenia, że każdy produkt ma
     *  stały VAT, jednakże zostawiłem furtkę która umożliwia na zastosowanie różnych VATów
     *
     * @inheritDoc
     */
    public function calculate(float $price, PurchaseItem $purchaseItem): float
    {
        return $price * static::VAT;
    }
}
