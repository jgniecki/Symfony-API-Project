<?php

namespace App\Controller;

use App\Dto\PurchaseRequest;
use App\Entity\Purchase;
use App\Service\PurchaseCalculatorService;
use App\Service\PurchaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v2/purchase', name: 'api_v2_purchase_')]
class ApiPurchaseController extends AbstractController
{
    #[Route('/{id}', name: 'info', methods: ['GET'])]
    public function infoPurchase(?Purchase $purchase, PurchaseService $purchaseService): JsonResponse
    {
        if (!$purchase) {
            return $this->json(['error' => 'Not found purchase'], 404);
        }

        return $this->json($purchaseService->info($purchase));
    }

    #[Route('/{id}/details', name: 'details', methods: ['GET'])]
    public function detailsPurchase(?Purchase $purchase, PurchaseService $purchaseService, PurchaseCalculatorService $purchaseCalculatorService): JsonResponse
    {
        if (!$purchase) {
            return $this->json(['error' => 'Not found purchase'], 404);
        }

        return $this->json($purchaseService->details($purchase, $purchaseCalculatorService));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createPurchase(Request $request, PurchaseService $purchaseService, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        if ($request->getContentTypeFormat() != "json") {
            return $this->json(['error' => 'Unsupported media type'], 415);
        }

        $payload = $request->getPayload()->all();

        try {
            $dto = $serializer->denormalize($payload, PurchaseRequest::class);
        } catch (\Exception) {
            return $this->json(['error' => 'Bad request'], 400);
        }

        $violations = $validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return $this->json(['error' => $errors], 400);
        }

        $purchase = $purchaseService->create($dto);

        if ($purchase) {
            return $this->json($purchaseService->info($purchase));
        }

        return $this->json(['error' => 'Bad request'], 400);
    }
}
