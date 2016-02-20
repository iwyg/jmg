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
 * @interface ResourceInterface
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
interface ResourceInterface
{
    /**
     * Returns true if resource is local.
     *
     * @return boolean
     */
    public function isLocal();

    /**
     * Determines if the resource was modified.
     *
     * @param int $time unix timestamp.
     *
     * @return bool
     */
    public function isFresh($time = null);

    /**
     * getLastModified
     *
     * @return int
     */
    public function getLastModified();

    /**
     * Returns the resource contents
     *
     * @return string
     */
    public function getContents();

    /**
     * Returns the resource mime time
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Retrurns the resource path
     *
     * @return string
     */
    public function getPath();

    /**
     * @return string
     */
    public function getFileName();

    /**
     * @return string
     */
    public function getHash();
}
