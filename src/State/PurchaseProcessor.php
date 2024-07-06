<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PurchaseRequest;
use App\Dto\PurchaseResponse;
use App\Entity\Purchase;
use App\Service\PurchaseService;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PurchaseProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PurchaseService $purchaseService,
    )
    {
    }


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PurchaseResponse
    {
        /**
         * @var PurchaseRequest $data
         */
        $purchase = $this->purchaseService->create($data);

        if (!$purchase) {
            throw new NotFoundHttpException("Bad request");
        }

        return $this->purchaseService->info($purchase);
    }
}
