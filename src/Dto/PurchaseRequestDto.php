<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public readonly string $name,
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,
        #[Assert\Type('array')]
        #[Assert\Count(['min' => 1])]
        #[Assert\Valid]
        #[Assert\All([
            new Assert\Collection(
                fields: [
                    'productId' => [
                        new Assert\NotBlank(),
                        new Assert\Type('integer'),
                    ],
                    'quantity' => [
                        new Assert\NotBlank(),
                        new Assert\Positive(),
                        new Assert\Type('integer'),
                    ],
                ]
            ),
        ])]
        #[ApiProperty(
            openapiContext: [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'productId' => [
                            'type' => 'integer',
                        ],
                        'quantity' => [
                            'type' => 'integer',
                        ]
                    ],
                ],
            ]
        )]
        public readonly array  $items,
    ) {
    }
}
