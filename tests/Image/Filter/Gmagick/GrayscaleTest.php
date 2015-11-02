<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Image\Filter\Gmagick;

use Thapp\Jmg\Tests\TestHelperTrait;
use Thapp\Jmg\Image\Filter\Gmagick\Grayscale;
use Thapp\Jmg\Tests\Image\Filter\GrayscaleFilterTest;
/**
 * @class GrayscaleTest
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class GrayscaleTest extends GrayscaleFilterTest
{
    use TestHelperTrait;

    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\Image\Filter\Gmagick\AbstractGmagickFilter', $this->newGrayscale());
    }

    protected function newGrayscale()
    {
        return new Grayscale;
    }

    protected function getFilterInterface()
    {
        return 'Thapp\Image\Filter\Gmagick\Grayscale';
    }

    protected function setUp()
    {
        $this->skipIfGmagick();
    }
}
