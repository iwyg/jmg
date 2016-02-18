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
use Thapp\Jmg\Filter\AbstractFilter;
use Thapp\Jmg\Filter\FilterInterface;
use Thapp\Image\Driver\Gmagick\Image;

/**
 * @class AbstractImagickFilter
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AbstractGmagickFilter extends AbstractFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(ProcessorInterface $proc)
    {
        return $proc->getDriver() instanceof Image;
    }
}
