<?php

/**
 * This File is part of the Thapp\Jmg package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Cache;

use Thapp\Jmg\ProcessorInterface;

/**
 * @interface CacheInterface
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
interface CacheInterface
{
    /** @var bool */
    const CONTENT_STRING   = true;

    /** @var bool */
    const CONTENT_RESOURCE = false;

    /** @var int */
    const EXPIRY_NONE = -1;

    /**
     * Get a cached resource by key.
     *
     * @param string $key
     * @param bool $raw
     *
     * @return \Thapp\Image\Resource\ResourceInterface if $raw is set to true,
     * it should simple return the resource as string.
     */
    public function get($key, $raw = self::CONTENT_RESOURCE);

    /**
     * Create and bind a cached resource to an id.
     *
     * @param string $key
     * @param ProcessorInterface $proc
     *
     * @return void
     */
    public function set($key, ProcessorInterface $proc);

    /**
     * Checks if a key exists in cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Delete the whole cache.
     *
     * @return bool
     */
    public function purge();

    /**
     * Delete a cached group based in the image name.
     *
     * @param string $image
     *
     * @return bool
     */
    public function delete($image, $prefix = '');

    /**
     * Set the filename prefix.
     *
     * @param string $prefix
     *
     * @return void
     */
    public function setPrefix($prefix);

    /**
     * Get the filename prefix.
     *
     * @return string
     */
    public function getPrefix();

    /**
     * Sets the cache lifetime.
     *
     * @return void
     */
    public function setExpiry($expires);

    /**
     * Creates a cachekey for a given src path.
     *
     * @param string $src
     * @param string $prefix
     * @param string $fingerprint
     *
     * @return string
     */
    public function createKey($src, $prefix = '', $fingerprint = null);
}
