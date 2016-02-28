<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Resource;

/**
 * @interface ImageResourceInterface
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface ImageResourceInterface extends ResourceInterface
{
    /**
     * getWidth
     *
     * @return int
     */
    public function getWidth();

    /**
     * getHeight
     *
     * @return int
     */
    public function getHeight();

    /**
     * getColorSpace
     *
     * @return string
     */
    public function getColorSpace();
}
