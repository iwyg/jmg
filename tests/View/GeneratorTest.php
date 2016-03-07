<?php

/*
 * This File is part of the Thapp\Jmg\Tests\View package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\View;

use Thapp\Jmg\View\Generator;

/**
 * @class GeneratorTest
 *
 * @package Thapp\Jmg\Tests\View
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\View\Generator', new Generator($this->mockJmg()));
    }

    /** @test */
    public function itIsExpectedThat()
    {
        $string = '/some/image.jpg';

        $gen = new Generator($jmg = $this->mockJmg(), $this->mockTask());
        $jmg->method('apply')->willReturn($string);

        $this->assertSame($string, $gen->resize(500, 600));
        $this->assertSame($string, $gen->scale(500));
        $this->assertSame($string, $gen->pixel(10000));
        $this->assertSame($string, $gen->fit(400, 400));
        $this->assertSame($string, $gen->cropAndResize(400, 400));
        $this->assertSame($string, $gen->crop(400, 400));
        $this->assertSame($string, $gen->get());
    }

    /** @test */
    public function itFilterShouldReturnGenerator()
    {
        $gen = new Generator($jmg = $this->mockJmg());
        $this->assertSame($gen, $gen->filter('gs;c=1'));
    }

    protected function mockTask()
    {
        return $this->getMockBuilder('Thapp\Jmg\View\Task')->disableOriginalConstructor()->getMock();
    }

    protected function mockJmg()
    {
        return $this->getMockBuilder('Thapp\Jmg\View\Jmg')->disableOriginalConstructor()->getMock();
    }
}
