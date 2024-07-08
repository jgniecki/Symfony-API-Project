<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Factory;

use App\Dto\PurchaseRequestDto;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PurchaseFactory
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function create(PurchaseRequestDto $dto): ?Purchase
    {
        $purchase = new Purchase();
        $purchase->setCreatedAt(new DateTimeImmutable());
        $purchase->setCustomerEmail($dto->email);
        $purchase->setCustomerName($dto->name);

        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->persist($purchase);

            foreach ($dto->items as $item) {
                $product = $this->entityManager->getRepository(Product::class)->find($item['productId'], LockMode::OPTIMISTIC);
                if (!$product) {
                    throw new HttpException(400, 'Product not found');
                }

                $quantity = $item['quantity'];
                if ($product->getQuantity() < $quantity) {
                    throw new HttpException(400, 'Not enough product quantity available.');
                }

                $purchaseItem = new PurchaseItem();
                $purchaseItem->setProduct($product);
                $purchaseItem->setQuantity($quantity);
                $purchaseItem->setUnitPrice((string)$product->getPrice());
                $purchase->addPurchaseItem($purchaseItem);

                $this->entityManager->persist($purchaseItem);
                $product->setQuantity($product->getQuantity() - $quantity);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
            return $purchase;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            return null;
        }
    }
}
