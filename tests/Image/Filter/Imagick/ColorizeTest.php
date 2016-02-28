<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Image\Filter\Imagick;

use Thapp\Jmg\Tests\TestHelperTrait;
use Thapp\Jmg\Image\Filter\Imagick\Colorize;
use Thapp\Jmg\Tests\Image\Filter\ColorizeFilterTest;

/**
 * @class ColorizeTest
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ColorizeTest extends ColorizeFilterTest
{
    use TestHelperTrait;

    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\Image\Filter\Imagick\AbstractImagickFilter', $this->newColorize());
    }
    protected function newColorize()
    {
        return new Colorize;
    }

    protected function mockImage()
    {
        return $this->getMockBuilder('Thapp\Image\Driver\Imagick\Image')
            ->disableOriginalConstructor()->getMock();
    }

    protected function setUp()
    {
        $this->skipIfImagick();
    }
}
