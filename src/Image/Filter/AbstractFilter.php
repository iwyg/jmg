<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Image\Filter;

use Thapp\Image\Driver\ImageInterface;
use Thapp\Jmg\ProcessorInterface;
use Thapp\Jmg\Filter\AbstractFilter as BaseFilter;

/**
 * @class AbstractFilter
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AbstractFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function supports(ProcessorInterface $proc)
    {
        return $proc->getDriver() instanceof ImageInterface;
    }

}
