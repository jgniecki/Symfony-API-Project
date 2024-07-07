<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PurchaseResponseDto;
use App\Factory\PurchaseFactory;
use App\Factory\PurchaseResponseDtoFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PurchaseProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PurchaseFactory            $purchaseFactory,
        private readonly PurchaseResponseDtoFactory $purchaseResponseDtoFactory,
    ) {
    }


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PurchaseResponseDto
    {
        $purchase = $this->purchaseFactory->create($data);

        if (!$purchase) {
            throw new NotFoundHttpException("Bad request");
        }

        return $this->purchaseResponseDtoFactory->create($purchase);
    }
}
