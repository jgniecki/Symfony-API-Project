<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Purchase;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PurchaseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $purchase = new Purchase();
        $purchase->setCustomerEmail("cc@mail.com");
        $purchase->setCustomerName("John Doe");
        $purchase->setCreatedAt(new DateTimeImmutable());
        $manager->persist($purchase);
        $manager->flush();
    }
}
