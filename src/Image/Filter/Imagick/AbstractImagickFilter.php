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
use Thapp\Jmg\Filter\AbstractFilter;
use Thapp\Jmg\Filter\FilterInterface;

/**
 * @class AbstractImagickFilter
 *
 * @package Thapp\Jmg\Image\Filter\Imagick
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AbstractImagickFilter extends AbstractFilter implements FilterInterface
{
    public function supports(ProcessorInterface $proc)
    {
        return $proc->getDriver() instanceof \Thapp\Image\Driver\Imagick\Image;
    }
}
