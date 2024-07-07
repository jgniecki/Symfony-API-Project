<?php

namespace App\Controller;

use App\Dto\PurchaseRequestDto;
use App\Entity\Purchase;
use App\Factory\PurchaseDetailsResponseDtoFactory;
use App\Factory\PurchaseFactory;
use App\Factory\PurchaseResponseDtoFactory;
use Exception;
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
    public function infoPurchase(?Purchase $purchase, PurchaseResponseDtoFactory $purchaseResponseDtoFactory): JsonResponse
    {
        if (!$purchase) {
            return $this->json(['error' => 'Not found purchase'], 404);
        }

        return $this->json($purchaseResponseDtoFactory->create($purchase));
    }

    #[Route('/{id}/details', name: 'details', methods: ['GET'])]
    public function detailsPurchase(?Purchase $purchase, PurchaseDetailsResponseDtoFactory $purchaseDetailsResponseDtoFactory): JsonResponse
    {
        $purchaseDetailsResponseDtoFactory->create($purchase);

        if (!$purchase) {
            return $this->json(['error' => 'Not found purchase'], 404);
        }

        return $this->json($purchaseDetailsResponseDtoFactory->create($purchase));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createPurchase(
        Request                    $request,
        SerializerInterface        $serializer,
        ValidatorInterface         $validator,
        PurchaseFactory            $purchaseFactory,
        PurchaseResponseDtoFactory $purchaseResponseDtoFactory
    ): Response {
        if ($request->getContentTypeFormat() != "json") {
            return $this->json(['error' => 'Unsupported media type'], 415);
        }

        $payload = $request->getPayload()->all();

        try {
            $dto = $serializer->denormalize($payload, PurchaseRequestDto::class);
        } catch (Exception) {
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

        $purchase = $purchaseFactory->create($dto);

        if ($purchase) {
            return $this->json($purchaseResponseDtoFactory->create($purchase));
        }

        return $this->json(['error' => 'Bad request'], 400);
    }
}
