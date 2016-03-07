<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Cache;

use Thapp\Jmg\Resolver\PathResolverInterface;
use Thapp\Jmg\Resolver\CacheResolverInterface;

/**
 * @class CacheClearer
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class CacheClearer
{
    /** @var CacheResolverInterface */
    private $cacheResolver;

    /**
     * Constructor.
     *
     * @param CacheResolverInterface $cacheResolver
     */
    public function __construct(CacheResolverInterface $cacheResolver)
    {
        $this->cacheResolver = $cacheResolver;
    }

    /**
     * Clears cache for a given path.
     *
     * @param string $name
     *
     * @return bool
     */
    public function clear($name = null)
    {
        if (null === $name) {
            return $this->clearAll();
        }

        if (!$this->cacheResolver->has($name) || !$cache = $this->cacheResolver->resolve($name)) {
            return false;
        }

        if ($cache->purge()) {
            return true;
        }

        return false;
    }

    /**
     * Clears cache for a given image.
     *
     * @param string $image
     * @param string $prefix
     *
     * @return bool
     */
    public function clearImage($image, $prefix = null)
    {
        if (!$this->cacheResolver->has($prefix) || !$cache = $this->cacheResolver->resolve($prefix)) {
            return false;
        }

        return $cache->delete($image, $prefix);
    }

    /**
     * clearAll
     *
     * @return boll
     */
    private function clearAll()
    {
        $cleared = [];

        foreach ($this->cacheResolver as $name => $cache) {
            if (in_array($cache, $cleared)) {
                continue;
            }

            $this->clear($name);
            $cleared[] = $cache;
        }

        return true;
    }
}
