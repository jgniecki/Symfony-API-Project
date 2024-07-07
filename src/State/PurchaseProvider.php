<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\PurchaseResponseDto;
use App\Factory\PurchaseResponseDtoFactory;
use App\Repository\PurchaseRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<PurchaseResponseDto>
 */
class PurchaseProvider implements ProviderInterface
{
    public function __construct(
        private readonly PurchaseRepository         $purchaseRepository,
        private readonly PurchaseResponseDtoFactory $purchaseResponseDtoFactory,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PurchaseResponseDto
    {
        $purchase = $this->purchaseRepository->find($uriVariables['id']);

        if (!$purchase) {
            throw new NotFoundHttpException('Not found purchase');
        }

        return $this->purchaseResponseDtoFactory->create($purchase);
    }
}
