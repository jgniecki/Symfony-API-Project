<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class Purchase
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param \App\Entity\Purchase $purchaseEntity
     * @param ArrayCollection<Product> $products
     * @return bool
     */
    public function updateProductQuantities(\App\Entity\Purchase $purchaseEntity, array $products): bool
    {
        $this->entityManager->beginTransaction();

        try {
            foreach ($products as $product) {
                $product = $item->getProduct();
                $orderedQuantity = $item->getQuantity();

                if ($product->getQuantity() < $orderedQuantity) {
                    throw new \Exception('Not enough product quantity available.');
                }

                $product->setQuantity($product->getQuantity() - $orderedQuantity);
                $this->entityManager->persist($product);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
            return true;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            // Log the exception or handle it as needed
            return false;
        }
    }
}