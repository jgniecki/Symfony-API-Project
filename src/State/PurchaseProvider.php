<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\PurchaseResponse;
use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use App\Service\PurchaseService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class PurchaseProvider implements ProviderInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly PurchaseRepository $purchaseRepository,
        private readonly PurchaseService $purchaseService,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PurchaseResponse
    {
        $purchase = $this->purchaseRepository->find($uriVariables['id']);

        /** @var Purchase $book */
        if (!$purchase) {
            throw new NotFoundHttpException('Purchase not found');
        }

        return $this->purchaseService->info($purchase);
    }
}
