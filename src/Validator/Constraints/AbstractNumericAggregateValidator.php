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

use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Kasper Hansen <kasper.h90@gmail.com>
 */
abstract class AbstractNumericAggregateValidator extends ConstraintValidator
{
    protected const FLOAT_TOLERANCE = 1e-12;

    protected float $sum = 0.0;

    protected int $count = 0;

    final public function computeAggregate(mixed $value, string $notNumericMessage, string $notNumericError): bool
    {
        $this->sum = 0.0;
        $this->count = 0;

        foreach ($value as $item) {
            if (!is_numeric($item)) {
                $this->context->buildViolation($notNumericMessage)
                    ->setParameter('{{ value }}', $this->formatValue($item))
                    ->setInvalidValue($value)
                    ->setCode($notNumericError)
                    ->addViolation();

                return false;
            }

            $this->sum += (float) $item;
            ++$this->count;
        }

        return true;
    }
}
