<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/purchase/{id}', name: 'info', methods: ['GET'])]
    public function info(?Purchase $purchase): JsonResponse
    {
        if (!$purchase) {
            return $this->json(['error' => 'Not found purchase'], 404);
        }

        $items = [];
        foreach ($purchase->getPurchaseItems() as $purchaseItem) {
            $items[] = [
                'product_id' => $purchaseItem->getProduct()->getId(),
                'quantity' => $purchaseItem->getQuantity(),
                'unitPrice' => (float)$purchaseItem->getUnitPrice(),
            ];
        }

        $result = [
            'id' => $purchase->getId(),
            'items' => $items,
        ];

        return $this->json($result, 200);
    }

    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
    public function createPurchase(EntityManagerInterface $entityManager): JsonResponse
    {
        $purchaseItems = [
            ['product_id' => 1, 'quantity' => 1],
        ];

        foreach ($purchaseItems as $purchaseItem) {}

        $products = $entityManager->getRepository(Product::class)->findAll();


        

        return $this->json($result, 200);
    }
}
