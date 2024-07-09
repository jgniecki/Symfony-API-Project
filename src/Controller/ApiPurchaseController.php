<?php declare(strict_types=1);

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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v2/purchase', name: 'api_v2_purchase_')]
class ApiPurchaseController extends AbstractController
{
    #[Route('/{id}', name: 'info', methods: ['GET'])]
    public function infoPurchase(?Purchase $purchase, PurchaseResponseDtoFactory $purchaseResponseDtoFactory): JsonResponse
    {
        if (!$purchase) {
            throw new NotFoundHttpException('Not found purchase');
        }

        return $this->json($purchaseResponseDtoFactory->create($purchase));
    }

    #[Route('/{id}/details', name: 'details', methods: ['GET'])]
    public function detailsPurchase(?Purchase $purchase, PurchaseDetailsResponseDtoFactory $purchaseDetailsResponseDtoFactory): JsonResponse
    {
        if (!$purchase) {
            throw new NotFoundHttpException('Not found purchase');
        }

        return $this->json($purchaseDetailsResponseDtoFactory->create($purchase));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createPurchase(
        Request                    $request,
        DenormalizerInterface      $serializer,
        ValidatorInterface         $validator,
        PurchaseFactory            $purchaseFactory,
        PurchaseResponseDtoFactory $purchaseResponseDtoFactory
    ): Response {
        if ($request->getContentTypeFormat() != "json") {
            throw new UnsupportedMediaTypeHttpException('Unsupported media type');
        }

        $payload = $request->getPayload()->all();

        try {
            $dto = $serializer->denormalize($payload, PurchaseRequestDto::class);
        } catch (Exception) {
            throw new BadRequestHttpException('Bad request');
        }

        if (!$dto instanceof PurchaseRequestDto) {
            throw new BadRequestHttpException('Bad request');
        }

        $violations = $validator->validate($dto);
        if (count($violations) > 0) {
            throw new BadRequestHttpException('Bad request');
        }

        $purchase = $purchaseFactory->create($dto);

        if ($purchase) {
            return $this->json($purchaseResponseDtoFactory->create($purchase));
        }

        throw new UnprocessableEntityHttpException('Failed to create purchase');
    }
}
