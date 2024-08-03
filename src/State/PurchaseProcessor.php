<?php declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PurchaseRequestDto;
use App\Dto\PurchaseResponseDto;
use App\Factory\PurchaseFactory;
use App\Factory\PurchaseResponseDtoFactory;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


/**
 * @implements ProcessorInterface<PurchaseRequestDto, PurchaseResponseDto>
 */
class PurchaseProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PurchaseFactory            $purchaseFactory,
        private readonly PurchaseResponseDtoFactory $purchaseResponseDtoFactory,
        private readonly TokenStorageInterface      $tokenStorage
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PurchaseResponseDto
    {
        $purchase = $this->purchaseFactory->create($data, $this->tokenStorage->getToken()->getUser());

        if (!$purchase) {
            throw new UnprocessableEntityHttpException("Failed to create purchase");
        }

        return $this->purchaseResponseDtoFactory->create($purchase);
    }
}
