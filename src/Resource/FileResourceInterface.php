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
 * @interface FileResourceInterface
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface FileResourceInterface extends ResourceInterface
{
    /**
     * getHandle
     *
     * @return resource
     */
    public function getHandle();

    /**
     * isValid
     *
     * @return void
     */
    public function isValid();
}
