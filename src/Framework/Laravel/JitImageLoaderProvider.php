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

use Thapp\Jmg\Resolver\LoaderResolverInterface;

/**
 * @class JmgFilterProvider
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class JmgLoaderProvider extends AppProvider
{
    /**
     * {@inheritdoc}
     */
    final public function boot()
    {
        $this->registerLoaders($this->app['jmg.loaders']);
    }

    /**
     * Register custom loaders
     *
     * @param LoaderResolverInterface $loaders
     *
     * @return void
     */
    abstract protected function registerLoaders(LoaderResolverInterface $loaders);
}
