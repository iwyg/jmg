<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Filter;

/**
 * @trait ModulateFilterTrait
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
trait ModulateFilterTrait
{
    /**
     * {@inheritdoc}
     */
    protected function parseOption($option, $value)
    {
        return (float)$value;
    }

    /**
     * {@inheritdoc}
     */
    protected function getShortOpts()
    {
        return ['b' => 'brightness', 's' => 'satturation', 'h' => 'hue'];
    }
}
