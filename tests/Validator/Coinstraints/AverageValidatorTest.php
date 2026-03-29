<?php

/*
 * This file is part of the Symfony Extra Constraints package.
 *
 * (c) Kasper Hansen <kasper.h90@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kasperh90\SymfonyExtraConstraints\Tests\Validator\Constraints;

use Kasperh90\SymfonyExtraConstraints\Validator\Constraints\Average;
use Kasperh90\SymfonyExtraConstraints\Validator\Constraints\AverageValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class AverageValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): AverageValidator
    {
        return new AverageValidator();
    }

    public function testNullIsValid()
    {
        $this->validator->validate(null, new Average(exactly: 2));
        $this->assertNoViolation();
    }

    public function testInvalidConstraint()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate([], new NotBlank());
    }

    public function testNonIterable()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate('non-iterable', new Average(exactly: 2));
    }

    public function testDivisionByZero()
    {
        $constraint = new Average(exactly: 2);
        $this->validator->validate([], $constraint);

        $this->buildViolation($constraint->divisionByZeroMessage)
            ->setParameter('{{ value }}', '[]')
            ->setInvalidValue([])
            ->setCode(Average::DIVISION_BY_ZERO_ERROR)
            ->assertRaised();
    }

    public static function getNonNumericValues()
    {
        return [
            [[1, 'a', 3], 'a'],
            [['a' => 1, 'b' => 'x', 'c' => 3], 'x'],
            [new \ArrayIterator([1, 'a', 3]), 'a'],
            [static fn () => (static function () {
                yield 1;
                yield 'a';
                yield 3;
            })(), 'a'],
        ];
    }

    #[DataProvider('getNonNumericValues')]
    public function testNonNumericValue(iterable|callable $values, string $invalid)
    {
        $values = \is_callable($values) ? $values() : $values;
        $constraint = new Average(min: 0);
        $this->validator->validate($values, $constraint);

        $this->buildViolation($constraint->notNumericMessage)
            ->setParameter('{{ value }}', '"'.$invalid.'"')
            ->setInvalidValue($values)
            ->setCode(Average::NOT_NUMERIC_ERROR)
            ->assertRaised();
    }

    public function testMinEqualsMaxActsAsExact()
    {
        $values = [1, 2, 3];
        $constraint = new Average(min: 2, max: 2);
        $this->validator->validate($values, $constraint);

        $this->assertNoViolation();
    }

    public function testMinEqualsMaxViolation()
    {
        $values = [1, 2, 3];
        $constraint = new Average(min: 3, max: 3);
        $this->validator->validate($values, $constraint);

        $this->buildViolation($constraint->exactMessage)
            ->setParameter('{{ average }}', 2)
            ->setParameter('{{ limit }}', 3)
            ->setInvalidValue($values)
            ->setCode(Average::NOT_EQUAL_AVERAGE_ERROR)
            ->assertRaised();
    }

    private function getAverage(iterable $values)
    {
        $sum = 0.0;
        $count = 0;

        foreach ($values as $value) {
            $sum += (float) $value;
            ++$count;
        }

        return 0 === $count ? 0.0 : $sum / $count;
    }

    private static function get123Generator()
    {
        yield 1;
        yield 2;
        yield 3;
    }

    public static function getValidAverageValues()
    {
        return [
            [[1, 2, 3], 2],
            [[1, 2, 3, -4], 0.5],
            [[-1, -2, -3], -2],
            [[1.5, 2.5, 3], 2.3333333333333],
            [[1.5, 2.5, 3.5], 2.5],
            [[1.5, 2.5, -3.5], 0.1666666666667],
            [[-1.5, -2.5, -3.5], -2.5],
            [[0.1, 0.2, 0.3], 0.2],
            [[0.0000001, 0.0000002], 0.00000015],
            [['1', '2', '3'], 2],
            [['1.5', '2.5'], 2.0],
            [['-1', '-2', '-3'], -2],
            [['a' => 1, 'b' => 2, 'c' => 3], 2],
            [[1, '2', 3.0], 2],
            [new \ArrayIterator([1, 2, 3]), 2],
            [static fn () => self::get123Generator(), 2],
        ];
    }

    #[DataProvider('getValidAverageValues')]
    public function testValidValuesForExactAverage(iterable|callable $values, int|float $average)
    {
        $constraint = new Average(exactly: $average);
        $this->validator->validate(\is_callable($values) ? $values() : $values, $constraint);

        $this->assertNoViolation();
    }

    public static function getInvalidAverageValues()
    {
        return [
            [[1, 2, 3], 1],
            [[1, 2, 3], 3],
            [[1, 2, 3, -4], 1],
            [[-1, -2, -3], -1],
            [[1.5, 2.5, 3], 2],
            [[1.5, 2.5, 3.5], 3],
            [[1.5, 2.5, -3.5], 1],
            [[-1.5, -2.5, -3.5], -1],
            [[0.1, 0.2, 0.3], 0.3],
            [[0.0000001, 0.0000002], 0.0000002],
            [['1', '2', '3'], 3],
            [['1.5', '2.5'], 3.0],
            [['-1', '-2', '-3'], -1],
            [['a' => 1, 'b' => 2, 'c' => 3], 3],
            [[1, '2', 3.0], 3],
            [new \ArrayIterator([1, 2, 3]), 3],
            [static fn () => self::get123Generator(), 3],
        ];
    }

    #[DataProvider('getInvalidAverageValues')]
    public function testInvalidValuesForExactAverage(iterable|callable $values, int|float $average)
    {
        $constraint = new Average(exactly: $average);
        $this->validator->validate(\is_callable($values) ? $values() : $values, $constraint);

        $this->buildViolation($constraint->exactMessage)
            ->setParameter('{{ average }}', $this->getAverage(\is_callable($values) ? $values() : $values))
            ->setParameter('{{ limit }}', $average)
            ->setInvalidValue(\is_callable($values) ? $values() : $values)
            ->setCode(Average::NOT_EQUAL_AVERAGE_ERROR)
            ->assertRaised();
    }

    public static function getValidMinAverageValues()
    {
        return [
            [[1, 2, 3], 2],
            [[1, 2, 3, -4], 0.5],
            [[-1, -2, -3], -2],
            [[1.5, 2.5, 3], 2.3333333333333],
            [[1.5, 2.5, 3.5], 2.5],
            [[1.5, 2.5, -3.5], 0.1666666666667],
            [[-1.5, -2.5, -3.5], -2.5],
            [[0.1, 0.2, 0.3], 0.2],
            [[0.0000001, 0.0000002], 0.00000015],
            [[0.0000001, 0.0000002, -0.0000001], 0.0000000666667],
            [['1', '2', '3'], 2],
            [['1.5', '2.5'], 2.0],
            [['-1', '-2', '-3'], -2],
            [['a' => 1, 'b' => 2, 'c' => 3], 2],
            [['a' => 1.5, 'b' => 2.5], 2.0],
            [[1, '2', 3.0], 2],
            [new \ArrayIterator([1, 2, 3]), 2],
            [self::get123Generator(), 2],
        ];
    }

    #[DataProvider('getValidMinAverageValues')]
    public function testValidValuesForMinAverage(iterable|callable $values, int|float $min)
    {
        $constraint = new Average(min: $min);
        $this->validator->validate(\is_callable($values) ? $values() : $values, $constraint);

        $this->assertNoViolation();
    }

    public static function getInvalidMinAverageValues()
    {
        return [
            [[1, 2, 3], 5],
            [[1, 2, 3, -4], 1],
            [[-1, -2, -3], -1],
            [[1.5, 2.5, 3], 3],
            [[1.5, 2.5, 3.5], 3],
            [[1.5, 2.5, -3.5], 1],
            [[-1.5, -2.5, -3.5], -1],
            [[0.1, 0.2, 0.3], 0.3],
            [[0.0000001, 0.0000002], 0.0000002],
            [[0.0000001, 0.0000002, -0.0000001], 0.0000001],
            [['1', '2', '3'], 3],
            [['1.5', '2.5'], 3.0],
            [['-1', '-2', '-3'], -1],
            [['a' => 1, 'b' => 2, 'c' => 3], 3],
            [['a' => 1.5, 'b' => 2.5], 3.0],
            [[1, '2', 3.0], 3],
            [new \ArrayIterator([1, 2, 3]), 3],
            [static fn () => self::get123Generator(), 5],
        ];
    }

    #[DataProvider('getInvalidMinAverageValues')]
    public function testInvalidValuesForMinAverage(iterable|callable $values, int|float $min)
    {
        $constraint = new Average(min: $min);
        $this->validator->validate(\is_callable($values) ? $values() : $values, $constraint);

        $this->buildViolation($constraint->minMessage)
            ->setParameter('{{ average }}', $this->getAverage(\is_callable($values) ? $values() : $values))
            ->setParameter('{{ limit }}', $min)
            ->setInvalidValue(\is_callable($values) ? $values() : $values)
            ->setCode(Average::TOO_LOW_ERROR)
            ->assertRaised();
    }

    public static function getValidMaxAverageValues()
    {
        return [
            [[1, 2, 3], 3],
            [[1, 2, 3, -4], 1],
            [[-1, -2, -3], -1],
            [[1.5, 2.5, 3], 2.5],
            [[1.5, 2.5, 3.5], 4],
            [[1.5, 2.5, -3.5], 1],
            [[-1.5, -2.5, -3.5], 1],
            [[0.1, 0.2, 0.3], 0.3],
            [[0.0000001, 0.0000002], 0.0000005],
            [[0.0000001, 0.0000002, -0.0000001], 0.0000002],
            [['1', '2', '3'], 3],
            [['1.5', '2.5'], 3.0],
            [['-1', '-2', '-3'], -1],
            [['a' => 1, 'b' => 2, 'c' => 3], 3],
            [['a' => 1.5, 'b' => 2.5], 3.0],
            [[1, '2', 3.0], 3],
            [new \ArrayIterator([1, 2, 3]), 3],
            [static fn () => self::get123Generator(), 3],
        ];
    }

    #[DataProvider('getValidMaxAverageValues')]
    public function testValidValuesForMaxAverage(iterable|callable $values, int|float $max)
    {
        $constraint = new Average(max: $max);
        $this->validator->validate(\is_callable($values) ? $values() : $values, $constraint);

        $this->assertNoViolation();
    }

    public static function getInvalidMaxAverageValues()
    {
        return [
            [[1, 2, 3], 1],
            [[1, 2, 3, -4], -5],
            [[-1, -2, -3], -11],
            [[1.5, 2.5, 3], 1.2],
            [[1.5, 2.5, -3.5], -1.1],
            [[0.1, 0.2, 0.3], 0.1],
            [[0.0000001, 0.0000002], 0.0000001],
            [[0.0000001, 0.0000002, -0.0000001], -0.0000001],
            [['1', '2', '3'], 1],
            [['1.5', '2.5', '3'], 0.5],
            [['-1', '-2', '-3'], -10],
            [['a' => 1, 'b' => 2, 'c' => 3], 0],
            [['a' => 1.5, 'b' => 2.5, 'c' => 3], -1],
            [[1, '2', 3.0], 0],
            [new \ArrayIterator([1, 2, 3]), 1],
            [static fn () => self::get123Generator(), 1],
        ];
    }

    #[DataProvider('getInvalidMaxAverageValues')]
    public function testInvalidValuesForMaxAverage(iterable|callable $values, int|float $max)
    {
        $constraint = new Average(max: $max);
        $this->validator->validate(\is_callable($values) ? $values() : $values, $constraint);

        $this->buildViolation($constraint->maxMessage)
            ->setParameter('{{ average }}', $this->getAverage(\is_callable($values) ? $values() : $values))
            ->setParameter('{{ limit }}', $max)
            ->setInvalidValue(\is_callable($values) ? $values() : $values)
            ->setCode(Average::TOO_HIGH_ERROR)
            ->assertRaised();
    }
}
