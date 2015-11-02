<?php

/*
 * This File is part of the Thapp\Jmg\Tests\Image\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Image\Filter;

/**
 * @class FilterTest
 *
 * @package Thapp\Jmg\Tests\Image\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class FilterTest extends \PHPUnit_Framework_TestCase
{

    protected function mockProc()
    {
        return $this->getMockBuilder('Thapp\Jmg\ProcessorInterface')
            ->disableOriginalConstructor()->getMock();
    }

    protected function mockImage()
    {
        return $this->getMockBuilder('Thapp\Image\Driver\ImageInterface')
            ->disableOriginalConstructor()->getMock();
    }

    protected function mockPalette()
    {
        return $this->getMockBuilder('Thapp\Image\Color\Palette\PaletteInterface')
            ->disableOriginalConstructor()->getMock();
    }

    protected function mockColor()
    {
        return $this->getMockBuilder('Thapp\Image\Color\ColorInterface')
            ->disableOriginalConstructor()->getMock();
    }
}
