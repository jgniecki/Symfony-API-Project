<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PurchaseItemFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $purchase = $manager->getRepository(Purchase::class)->findOneBy(['customerEmail' => "cc@mail.com"]);

        for ($i = 0; $i < 3; $i++) {
            $product = $manager->getRepository(Product::class)->findOneBy(['name' => 'Product_' . $i]);
            if ($product === null) {
                continue;
            }
            $purchaseItem = new PurchaseItem();
            $purchaseItem->setProduct($product);
            $purchaseItem->setQuantity(mt_rand(1, 3));
            $purchaseItem->setPurchase($purchase);
            $purchaseItem->setUnitPrice((string)$product->getPrice());
            $manager->persist($purchaseItem);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PurchaseFixtures::class,
            ProductFixtures::class,
        ];
    }
}
