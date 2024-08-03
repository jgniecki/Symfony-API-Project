<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Service\Calculator;

use App\Entity\PurchaseItem;

interface PriceCalculatorInterface
{
    public function calculate(float $price, PurchaseItem $purchaseItem): float;
}
