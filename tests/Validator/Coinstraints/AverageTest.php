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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\AttributeLoader;

class AverageTest extends TestCase
{
    public function testAttribute()
    {
        $metadata = new ClassMetadata(AverageDummy::class);
        $loader = new AttributeLoader();
        $this->assertTrue($loader->loadClassMetadata($metadata));

        [$aConstraint] = $metadata->getPropertyMetadata('a')[0]->getConstraints();
        $this->assertSame(10.0, $aConstraint->min);
        $this->assertSame(10.0, $aConstraint->max);
        $this->assertSame('myExactMessage', $aConstraint->exactMessage);

        [$bConstraint] = $metadata->getPropertyMetadata('b')[0]->getConstraints();
        $this->assertSame(10.0, $bConstraint->min);
        $this->assertSame(100.0, $bConstraint->max);
        $this->assertSame('myMinMessage', $bConstraint->minMessage);
        $this->assertSame('myMaxMessage', $bConstraint->maxMessage);
        $this->assertSame(['Default', 'AverageDummy'], $bConstraint->groups);

        [$cConstraint] = $metadata->getPropertyMetadata('c')[0]->getConstraints();
        $this->assertSame(10.0, $cConstraint->min);
        $this->assertSame(10.0, $cConstraint->max);
        $this->assertSame(['my_group'], $cConstraint->groups);
        $this->assertSame('some attached data', $cConstraint->payload);
    }

    public function testMissinngOptions()
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage(\sprintf('Either option "min" or "max" must be given for constraint "%s".', Average::class));

        new Average();
    }
}

class AverageDummy
{
    #[Average(exactly: 10, exactMessage: 'myExactMessage')]
    private $a;

    #[Average(min: 10, max: 100, minMessage: 'myMinMessage', maxMessage: 'myMaxMessage')]
    private $b;

    #[Average(exactly: 10.0, groups: ['my_group'], payload: 'some attached data')]
    private $c;
}
