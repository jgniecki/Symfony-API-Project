<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Factory;

use App\Dto\PurchaseResponseDto;
use App\Entity\Purchase;

class PurchaseResponseDtoFactory
{
    public function create(Purchase $purchase): PurchaseResponseDto
    {
        return new PurchaseResponseDto(
            id: $purchase->getId(),
            items: array_map(function($item) {
                return [
                    'productId' => $item->getProduct()->getId(),
                    'quantity' => $item->getQuantity(),
                    'unitPrice' => $item->getUnitPrice(),
                ];
            }, $purchase->getPurchaseItems()->toArray())
        );
    }
}