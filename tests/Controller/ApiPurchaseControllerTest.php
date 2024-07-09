<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\ProductFixtures;
use App\DataFixtures\PurchaseFixtures;
use App\DataFixtures\PurchaseItemFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Kernel;
use App\Service\PurchaseCalculatorService;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\Test\ResetDatabase;

class ApiPurchaseControllerTest extends ApiTestCase
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
        $purchase = $entityManager->getRepository(Purchase::class)->findOneBy(['customerEmail' => 'cc@mail.com']);
        $response = static::createClient()->request('GET', '/api/v2/purchase/' . $purchase->getId());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
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
        $response = static::createClient()->request('GET', '/api/v2/purchase/-1');
        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(['error' => 'Not found purchase']);
    }

    public function testDetails(): void
    {
        $calculator = static::getContainer()->get(PurchaseCalculatorService::class);
        /** @var EntityManager $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $purchase = $entityManager->getRepository(Purchase::class)->findOneBy(['customerEmail' => 'cc@mail.com']);
        $response = static::createClient()->request('GET', '/api/v2/purchase/' . $purchase->getId() . '/details');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
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
        $response = static::createClient()->request('GET', '/api/v2/purchase/-1');
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

        $response = static::createClient()->request('POST', '/api/v2/purchase', [
            'headers' => [
                'Authorization' => 'Bearer test_apiToken',
            ],
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
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $this->assertJsonContains([
            'id' => $purchase->getId(),
            'items' => array_map(function ($item) {
                return [
                    'productId' => (int)$item->getProduct()->getId(),
                    'quantity' => 1
                ];
            }, $purchase->getPurchaseItems()->toArray())
        ]);
    }

    public function testCreateInvalidContentType(): void
    {
        $response = static::createClient()->request('POST', '/api/v2/purchase', [
            'headers' => [
                'Authorization' => 'Bearer test_apiToken',
                'Accept' => 'application/json+ld',
            ],
        ]);

        $this->assertResponseStatusCodeSame(415);
        $this->assertJsonContains(['error' => 'Unsupported media type']);
    }

    public function testCreateInvalidBody(): void
    {
        $response = static::createClient()->request('POST', '/api/v2/purchase', [
            'headers' => [
                'Authorization' => 'Bearer test_apiToken',
            ],
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['error' => 'Bad request']);
    }

    public function testCreateInvalidProduct(): void
    {
        $response = static::createClient()->request('POST', '/api/v2/purchase', [
            'headers' => [
                'Authorization' => 'Bearer test_apiToken',
            ],
            'json' => [
                'email' => 'test@mail.com',
                'name' => 'Test Name',
                'items' => [
                    ['productId' => -1, 'quantity' => 1]
                ]
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(['error' => 'Product not found']);
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

        $response = static::createClient()->request('POST', '/api/v2/purchase', [
            'headers' => [
                'Authorization' => 'Bearer test_apiToken',
            ],
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

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(['error' => 'Not enough product quantity available']);
    }

    public function testCreateNotApiToken(): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var Product[] $products */
        $products = $entityManager->getRepository(Product::class)->findAll();

        $product = $products[0];
        $product->setQuantity(0);
        $entityManager->flush();

        $response = static::createClient()->request('POST', '/api/v2/purchase', [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(['error' => 'Full authentication is required to access this resource.']);
    }

    public function testCreateInvalidApiToken(): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        /** @var Product[] $products */
        $products = $entityManager->getRepository(Product::class)->findAll();

        $product = $products[0];
        $product->setQuantity(0);
        $entityManager->flush();

        $response = static::createClient()->request('POST', '/api/v2/purchase', [
            'headers' => [
                'Authorization' => 'Bearer token',
            ],
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(['error' => 'Invalid Api Token']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $loader = new Loader();
        $loader->addFixture(new UserFixtures());
        $loader->addFixture(new ProductFixtures());
        $loader->addFixture(new PurchaseFixtures());
        $loader->addFixture(new PurchaseItemFixtures());
        $purger = new ORMPurger();
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
