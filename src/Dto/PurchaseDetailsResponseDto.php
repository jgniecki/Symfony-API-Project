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

class PurchaseDetailsResponseDto
{
    /**
     * @param int $id
     * @param array<array{productId: int, quantity: int, unitPrice: numeric-string}> $items
     * @param array{price: numeric-string, vat: numeric-string, quantity: int} $total
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public readonly int   $id,
        #[Assert\Type('array')]
        #[Assert\Count(['min' => 1])]
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
                    'unitPrice' => [
                        new Assert\Regex('/^(0|[1-9]\d*)\.\d{1,2}$/'),
                        new Assert\NotBlank(),
                        new Assert\Type('string'),
                    ],
                ]
            ),
        ])]
        #[ApiProperty(
            required: true,
            openapiContext: [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'productId' => [
                            'type' => 'integer',
                            'example' => 1,
                        ],
                        'quantity' => [
                            'type' => 'integer',
                            'example' => 2,
                        ],
                        'unitPrice' => [
                            'type' => 'string',
                            'example' => "1.00",
                        ],
                    ],
                ],
            ]
        )]
        public readonly array $items,
        #[Assert\NotBlank]
        #[Assert\Valid]
        #[Assert\Collection(
            fields: [
                'price' => [
                    new Assert\Regex('/^(0|[1-9]\d*)\.\d{1,2}$/'),
                    new Assert\NotBlank(),
                    new Assert\NotNull(),
                    new Assert\Type('string')

                ],
                'vat' => [
                    new Assert\Regex('/^(0|[1-9]\d*)\.\d{1,2}$/'),
                    new Assert\NotBlank(),
                    new Assert\NotNull(),
                    new Assert\Type('string')
                ],
                'quantity' => [
                    new Assert\Type('integer'),
                    new Assert\Positive(),
                    new Assert\NotBlank()
                ]
            ],
        )]
        #[ApiProperty(
            required: true,
            openapiContext: [
                'type' => 'object',
                'properties' => [
                    'price' => [
                        'type' => 'string',
                        'example' => '2.00'
                    ],
                    'vat' => [
                        'type' => 'string',
                        'example' => '0.46'
                    ],
                    'quantity' => [
                        'type' => 'integer',
                        'example' => 2
                    ],
                ],
            ]
        )]
        public readonly array $total,
    ) {
    }
}
