<?php

/*
 * This file is part of the Symfony Extra Constraints package.
 *
 * (c) Kasper Hansen <kasper.h90@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kasperh90\SymfonyExtraConstraints\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * Validates the average of an iterable of numeric values.
 *
 * @author Kasper Hansen <kasper.h90@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Average extends Constraint
{
    public const NOT_NUMERIC_ERROR = 'ced65690-6059-42e0-af2f-beacdbe7a1d5';
    public const NOT_EQUAL_AVERAGE_ERROR = '8bbfd45e-a4b3-4119-9602-4d88133fc264';
    public const DIVISION_BY_ZERO_ERROR = 'd1c8b9e7-5a0c-4f1e-9b3c-2a1e5f8a9b6e';
    public const TOO_LOW_ERROR = '95d10c0f-12fb-42e9-8b1c-7e8fa14dc149';
    public const TOO_HIGH_ERROR = '824e0a3f-d8ca-4ec1-9149-f72f5d252640';

    protected const ERROR_NAMES = [
        self::NOT_NUMERIC_ERROR => 'NOT_NUMERIC_ERROR',
        self::NOT_EQUAL_AVERAGE_ERROR => 'NOT_EQUAL_AVERAGE_ERROR',
        self::DIVISION_BY_ZERO_ERROR => 'DIVISION_BY_ZERO_ERROR',
        self::TOO_LOW_ERROR => 'TOO_LOW_ERROR',
        self::TOO_HIGH_ERROR => 'TOO_HIGH_ERROR',
    ];

    public string $exactMessage = 'The average of the collection should be exactly {{ limit }}.';
    public string $minMessage = 'The average of the collection should be {{ limit }} or more.';
    public string $maxMessage = 'The average of the collection should be {{ limit }} or less.';
    public string $notNumericMessage = 'The collection contains non numeric value {{ value }}.';
    public string $divisionByZeroMessage = 'The value {{ value }} is not a valid division by zero.';
    public ?float $min = null;
    public ?float $max = null;

    /**
     * @param float|null    $exactly The exact expected average of the collection
     * @param float|null    $min     Minimum expected average of the collection
     * @param float|null    $max     Maximum expected average of the collection
     * @param string[]|null $groups
     */
    public function __construct(
        ?float $exactly = null,
        ?float $min = null,
        ?float $max = null,
        ?string $exactMessage = null,
        ?string $minMessage = null,
        ?string $maxMessage = null,
        ?string $notNumericMessage = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        if (null !== $exactly && null === $min && null === $max) {
            $min = $max = $exactly;
        }

        parent::__construct(null, $groups, $payload);

        $this->min = $min;
        $this->max = $max;
        $this->exactMessage = $exactMessage ?? $this->exactMessage;
        $this->minMessage = $minMessage ?? $this->minMessage;
        $this->maxMessage = $maxMessage ?? $this->maxMessage;
        $this->notNumericMessage = $notNumericMessage ?? $this->notNumericMessage;

        if (null === $this->min && null === $this->max) {
            throw new MissingOptionsException(\sprintf('Either option "min" or "max" must be given for constraint "%s".', __CLASS__), ['min', 'max']);
        }
    }
}
