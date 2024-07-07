<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Factory;

use App\Dto\PurchaseDetailsResponseDto;
use App\Entity\Purchase;
use App\Service\PurchaseCalculatorService;

class PurchaseDetailsResponseDtoFactory
{
    public function __construct(private readonly PurchaseCalculatorService $purchaseCalculatorService)
    {
    }

    public function create(Purchase $purchase, ?PurchaseResponseDtoFactory $purchaseResponseDtoFactory = null): PurchaseDetailsResponseDto
    {
        if (!$purchaseResponseDtoFactory) {
            $purchaseResponseDtoFactory = new PurchaseResponseDtoFactory();
        }

        $dto = $purchaseResponseDtoFactory->create($purchase);
        $total = [
            'quantity' => $this->purchaseCalculatorService->calculateTotalQuantity($purchase),
            'vat' => number_format($this->purchaseCalculatorService->calculateTotalVat($purchase), 2, '.', ''),
            'price' => number_format($this->purchaseCalculatorService->calculateTotalPrice($purchase), 2, '.', ''),
        ];

        return new PurchaseDetailsResponseDto($dto->id, $dto->items, $total);
    }
}
