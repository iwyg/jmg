<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Image\Filter\Gmagick;

use Thapp\Jmg\ProcessorInterface;
use Thapp\Image\Filter\Gmagick\Modulate as GmagickModulate;
use Thapp\Jmg\Image\Filter\ModulateFilterTrait;

/**
 * @class Modulate
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Modulate extends AbstractGmagickFilter
{
    use ModulateFilterTrait;

    /**
     * {@inheritdoc}
     */
    protected function newModulate($bri, $sat, $hue)
    {
        return new GmagickModulate($bri, $sat, $hue);
    }
}
