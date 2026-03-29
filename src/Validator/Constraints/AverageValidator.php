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
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @author Kasper Hansen <kasper.h90@gmail.com>
 */
class AverageValidator extends AbstractNumericAggregateValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Average) {
            throw new UnexpectedTypeException($constraint, Average::class);
        }

        if (null === $value) {
            return;
        }

        if (!is_iterable($value)) {
            throw new UnexpectedValueException($value, 'iterable');
        }

        if (!$this->computeAggregate($value, $constraint->notNumericMessage, Average::NOT_NUMERIC_ERROR)) {
            return;
        }

        try {
            $average = $this->sum / $this->count;
        } catch (\DivisionByZeroError) {
            $this->context->buildViolation($constraint->divisionByZeroMessage)
                ->setParameter('{{ value }}', '[]')
                ->setInvalidValue($value)
                ->setCode(Average::DIVISION_BY_ZERO_ERROR)
                ->addViolation();

            return;
        }

        $min = $constraint->min;
        $max = $constraint->max;

        $exactlyOptionEnabled = null !== $min
            && null !== $max
            && abs($min - $max) <= self::FLOAT_TOLERANCE;

        if ($exactlyOptionEnabled) {
            if (abs($average - $min) > self::FLOAT_TOLERANCE) {
                $this->context->buildViolation($constraint->exactMessage)
                    ->setParameter('{{ average }}', $average)
                    ->setParameter('{{ limit }}', $min)
                    ->setInvalidValue($value)
                    ->setCode(Average::NOT_EQUAL_AVERAGE_ERROR)
                    ->addViolation();
            }

            return;
        }

        if (null !== $max && $average - $max > self::FLOAT_TOLERANCE) {
            $this->context->buildViolation($constraint->maxMessage)
                ->setParameter('{{ average }}', $average)
                ->setParameter('{{ limit }}', $max)
                ->setInvalidValue($value)
                ->setCode(Average::TOO_HIGH_ERROR)
                ->addViolation();

            return;
        }

        if (null !== $min && $min - $average > self::FLOAT_TOLERANCE) {
            $this->context->buildViolation($constraint->minMessage)
                ->setParameter('{{ average }}', $average)
                ->setParameter('{{ limit }}', $min)
                ->setInvalidValue($value)
                ->setCode(Average::TOO_LOW_ERROR)
                ->addViolation();
        }
    }
}
