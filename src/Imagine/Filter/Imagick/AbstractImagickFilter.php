<?php

/*
 * This File is part of the Thapp\Jmg\Imagine\Filter\Imagick package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Imagine\Filter\Imagick;

use Imagine\Image\Imagick\Image;
use Thapp\Jmg\ProcessorInterface;
use Thapp\Jmg\Filter\FilterInterface;

/**
 * @class AbstractImagickFilter
 *
 * @package Thapp\Jmg\Imagine\Filter\Imagick
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class AbstractImagickFilter implements FilterInterface
{
    public function supports(ProcessorInterface $proc)
    {
        $proc->getCurrentImage() instanceof Image;
    }
}
