<?php

namespace ApiPlatform;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\ProductFixtures;
use App\DataFixtures\PurchaseFixtures;
use App\DataFixtures\PurchaseItemFixtures;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Kernel;
use App\Service\PurchaseCalculatorService;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Zenstruck\Foundry\Test\ResetDatabase;

class PurchaseTest extends ApiTestCase
{
    use ResetDatabase;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testInfo(): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $client = static::createClient();
        $purchase = $entityManager->getRepository(Purchase::class)->findOneBy(['customerEmail' => 'cc@mail.com']);
        $response = $client->request('GET', '/api/v1/purchase/' . $purchase->getId(), [
            'headers' => ['Accept' => 'application/json']
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        $this->assertJsonContains([
            'id' => $purchase->getId(),
            'items' => array_map(function ($item) {
                return [
                    'productId' => (int)$item->getProduct()->getId(),
                    'quantity' => (int)$item->getQuantity(),
                    'unitPrice' => $item->getUnitPrice(),
                ];
            }, $purchase->getPurchaseItems()->toArray())
        ]);
    }

    public function testInfoInvalidPurchase(): void
    {
        /** @var EntityManager $entityManager */
        $response = static::createClient()->request('GET', '/api/v1/purchase/-1', [
            'headers' => ['Accept' => 'application/json']
        ]);

        $this->assertJsonContains(['error' => 'Not found purchase']);
        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDetails(): void
    {
        $calculator = static::getContainer()->get(PurchaseCalculatorService::class);
        /** @var EntityManager $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $client = static::createClient();

        $purchase = $entityManager->getRepository(Purchase::class)->findOneBy(['customerEmail' => 'cc@mail.com']);
        $response = $client->request('GET', '/api/v1/purchase/' . $purchase->getId() . '/details', [
            'headers' => ['Accept' => 'application/json']
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        $this->assertJsonContains([
            'id' => $purchase->getId(),
            'items' => array_map(function ($item) {
                return [
                    'productId' => (int)$item->getProduct()->getId(),
                    'quantity' => (int)$item->getQuantity(),
                    'unitPrice' => $item->getUnitPrice(),
                ];
            }, $purchase->getPurchaseItems()->toArray()),
            'total' => [
                'price' => number_format($calculator->calculateTotalPrice($purchase), 2, '.', ''),
                'vat' => number_format($calculator->calculateTotalVat($purchase), 2, '.', ''),
                'quantity' => $calculator->calculateTotalQuantity($purchase),
            ]
        ]);
    }

    public function testDetailsInvalidPurchase(): void
    {
        /** @var EntityManager $entityManager */
        $response = static::createClient()->request('GET', '/api/v1/purchase/-1/details', [
            'headers' => ['Accept' => 'application/json']
        ]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(['error' => 'Not found purchase']);
    }

    public function testCreate(): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var Product[] $products */
        $products = $entityManager->getRepository(Product::class)->findAll();
        $client = static::createClient();

        $response = $client->request('POST', '/api/v1/purchase', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'email' => 'test@mail.com',
                'name' => 'Test Name',
                'items' => array_map(function ($item) {
                    return [
                        'productId' => $item->getId(),
                        'quantity' => 1,
                    ];
                }, $products),
            ]
        ]);

        $purchase = $entityManager->getRepository(Purchase::class)->findOneBy(['customerEmail' => 'test@mail.com']);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        $this->assertJsonContains([
            'id' => $purchase->getId(),
            'items' => array_map(function ($item) {
                return [
                    'productId' => (int)$item->getProduct()->getId(),
                    'quantity' => 1,
                ];
            }, $purchase->getPurchaseItems()->toArray())
        ]);
    }

    public function testCreateInvalidContentType(): void
    {
        $response = static::createClient()->request('POST', '/api/v1/purchase');

        $this->assertResponseStatusCodeSame(415);
    }

    public function testCreateInvalidBody(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $response = static::createClient()->request('POST', '/api/v1/purchase', [
            'headers' => ['Accept' => 'application/json'],
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['error' => 'Bad request']);
    }

    public function testCreateInvalidProduct(): void
    {
        $response = static::createClient()->request('POST', '/api/v1/purchase', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'email' => 'test@mail.com',
                'name' => 'Test Name',
                'items' => [
                    ['productId' => -1, 'quantity' => 1]
                ]
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(['error' => 'Failed to create purchase']);
    }

    public function testCreateTooSmallQuantityProduct(): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var Product[] $products */
        $products = $entityManager->getRepository(Product::class)->findAll();

        $product = $products[0];
        $product->setQuantity(0);
        $entityManager->flush();

        $response = static::createClient()->request('POST', '/api/v1/purchase', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'email' => 'test@mail.com',
                'name' => 'Test Name',
                'items' => array_map(function ($item) {
                    return [
                        'productId' => $item->getId(),
                        'quantity' => 1,
                    ];
                }, $products),
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(['error' => 'Failed to create purchase']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $loader = new Loader();
        $loader->addFixture(new ProductFixtures());
        $loader->addFixture(new PurchaseFixtures());
        $loader->addFixture(new PurchaseItemFixtures());
        $purger = new ORMPurger();
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
