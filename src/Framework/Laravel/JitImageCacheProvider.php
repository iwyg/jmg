<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Framework\Laravel;

use Thapp\Jmg\Resolver\CacheResolverInterface;

/**
 * @class JmgFilterProvider
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class JmgCacheProvider extends AppProvider
{
    /**
     * {@inheritdoc}
     */
    final public function boot()
    {
        $this->registerCaches($this->app['jmg.caches']);
    }

    /**
     * Registers a filter
     *
     * @param FilterResolverInterface $filters
     *
     * @return void
     */
    abstract protected function registerCaches(CacheResolverInterface $caches);
}
