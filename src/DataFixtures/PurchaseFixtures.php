<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Purchase;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PurchaseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->findOneBy(['username' => "admin"]);

        $purchase = new Purchase();
        $purchase->setCustomerEmail("cc@mail.com");
        $purchase->setCustomerName("John Doe");
        $purchase->setUser($user);
        $manager->persist($purchase);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
