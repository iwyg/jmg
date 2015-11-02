<?php

/*
 * This File is part of the Thapp\Jmg\Imagine\Filter\Gmagick package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Imagine\Filter\Gmagick;

use Imagine\Image\Gmagick\Image;
use Thapp\Jmg\ProcessorInterface;
use Thapp\Jmg\Filter\FilterInterface;

/**
 * @class AbstractGmagickFilter
 *
 * @package Thapp\Jmg\Imagine\Filter\Gmagick
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class AbstractGmagickFilter
{
    public function supports(ProcessorInterface $proc)
    {
        $proc->getCurrentImage() instanceof Image;
    }
}
