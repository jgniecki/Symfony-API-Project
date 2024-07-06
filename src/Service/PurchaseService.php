<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Dto\PurchaseDetailsResponse;
use App\Dto\PurchaseRequest;
use App\Dto\PurchaseResponse;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

class PurchaseService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function info(Purchase $purchase): PurchaseResponse
    {
        return new PurchaseResponse(
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

    public function details(Purchase $purchase, PurchaseCalculatorService $purchaseCalculatorService): PurchaseDetailsResponse
    {
        $dto = $this->info($purchase);

        $total = [
            'quantity' => $purchaseCalculatorService->calculateTotalQuantity($purchase),
            'vat' => number_format($purchaseCalculatorService->calculateTotalVat($purchase), 2, '.', ''),
            'price' => number_format($purchaseCalculatorService->calculateTotalPrice($purchase), 2, '.', ''),
        ];

        return new PurchaseDetailsResponse($dto->id, $dto->items, $total);
    }

    public function create(PurchaseRequest $dto): ?Purchase
    {
        $purchase = new Purchase();
        $purchase->setCreatedAt(new \DateTimeImmutable());
        $purchase->setCustomerEmail($dto->email);
        $purchase->setCustomerName($dto->name);

        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->persist($purchase);

            foreach ($dto->items as $item) {
                $product = $this->entityManager->getRepository(Product::class)->find($item['productId'], LockMode::OPTIMISTIC);
                if (!$product) {
                    throw new \Exception('Product not found');
                }

                $quantity = $item['quantity'];
                if ($product->getQuantity() < $quantity) {
                    throw new \Exception('Not enough product quantity available.');
                }

                $purchaseItem = new PurchaseItem();
                $purchaseItem->setProduct($product);
                $purchaseItem->setQuantity($quantity);
                $purchaseItem->setUnitPrice($product->getPrice());
                $purchase->addPurchaseItem($purchaseItem);

                $this->entityManager->persist($purchaseItem);
                $product->setQuantity($product->getQuantity() - $quantity);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
            return $purchase;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return null;
        }
    }
}