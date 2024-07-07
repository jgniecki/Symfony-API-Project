<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3; $i++) {
            $product = new Product();
            $price = number_format(mt_rand(10, 100), 2, '.', '');
            $product->setName('Product_' . $i);
            $product->setPrice($price);
            $product->setQuantity(10);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
