<?php

/*
 * This File is part of the Thapp\Jmg\Image\Filter\Imagick package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Image\Filter\Imagick;

use Thapp\Jmg\ProcessorInterface;
use Thapp\Image\Filter\Imagick\Modulate as ImagickModulate;
use Thapp\Jmg\Image\Filter\ModulateFilterTrait;

/**
 * @class Modulate
 *
 * @package Thapp\Jmg\Image\Filter\Imagick
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Modulate extends AbstractImagickFilter
{
    use ModulateFilterTrait;

    protected function newModulate($bri, $sat, $hue)
    {
        return new ImagickModulate($bri, $sat, $hue);
    }
}
