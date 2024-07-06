<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\PurchaseDetailsResponse;
use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use App\Service\PurchaseCalculatorService;
use App\Service\PurchaseService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PurchaseDetailsProvider implements ProviderInterface
{
    public function __construct(
        private readonly PurchaseRepository $purchaseRepository,
        private readonly PurchaseService $purchaseService,
        private readonly PurchaseCalculatorService $purchaseCalculatorService
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PurchaseDetailsResponse
    {
        $purchase = $this->purchaseRepository->find($uriVariables['id']);

        /** @var Purchase $book */
        if (!$purchase) {
            throw new NotFoundHttpException('Not found purchase');
        }

        return $this->purchaseService->details($purchase, $this->purchaseCalculatorService);
    }
}
