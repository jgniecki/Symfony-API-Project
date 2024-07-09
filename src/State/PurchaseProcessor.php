<?php declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PurchaseRequestDto;
use App\Dto\PurchaseResponseDto;
use App\Factory\PurchaseFactory;
use App\Factory\PurchaseResponseDtoFactory;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<PurchaseRequestDto, PurchaseResponseDto>
 */
class PurchaseProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PurchaseFactory            $purchaseFactory,
        private readonly PurchaseResponseDtoFactory $purchaseResponseDtoFactory,
    ) {
    }

    /**
     * @param PurchaseRequestDto $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return PurchaseResponseDto
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PurchaseResponseDto
    {
        $purchase = $this->purchaseFactory->create($data);

        if (!$purchase) {
            throw new UnprocessableEntityHttpException("Failed to create purchase");
        }

        return $this->purchaseResponseDtoFactory->create($purchase);
    }
}
