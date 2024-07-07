<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\PurchaseDetailsResponseDto;
use App\Factory\PurchaseDetailsResponseDtoFactory;
use App\Repository\PurchaseRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PurchaseDetailsProvider implements ProviderInterface
{
    public function __construct(
        private readonly PurchaseRepository                $purchaseRepository,
        private readonly PurchaseDetailsResponseDtoFactory $purchaseDetailsResponseDtoFactory,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PurchaseDetailsResponseDto
    {
        $purchase = $this->purchaseRepository->find($uriVariables['id']);

        if (!$purchase) {
            throw new NotFoundHttpException('Not found purchase');
        }

        return $this->purchaseDetailsResponseDtoFactory->create($purchase);
    }
}
