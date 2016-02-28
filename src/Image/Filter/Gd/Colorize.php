<?php

/*
 * This File is part of the Thapp\Jmg\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Image\Filter\Gd;

use Thapp\Jmg\ProcessorInterface;
use Thapp\Jmg\Image\Filter\ColorizeFilterTrait;
use Thapp\Image\Color\ColorInterface;
use Thapp\Image\Filter\Gd\Colorize as GdColorize;

/**
 * @class Greyscale
 *
 * @package Thapp\Jmg\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Colorize extends AbstractGdFilter
{
    use ColorizeFilterTrait;

    /**
     * {@inheritdoc}
     */
    protected function newFilter(ColorInterface $color)
    {
        return new GdColorize($color);
    }
}
