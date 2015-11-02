<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Framework\Laravel\Resolver;

use Thapp\Jmg\Resolver\LoaderResolver;
use Illuminate\Contracts\Foundation\Application;
use Thapp\Jmg\Framework\Common\Resolver\LazyResolverTrait;

/**
 * @class LazyLoaderResolver
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class LazyLoaderResolver extends LoaderResolver
{
    use LazyResolverTrait;

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($prefix)
    {
        if (null !== $loader = parent::resolve($prefix = trim($prefix, '/'))) {
            return $loader;
        }

        if (null === $loader = $this->resolveLoader($prefix)) {
            //throw new \InvalidArgumentException('No loader for prefix ' . $prefix);
            return;
        }

        $this->add($prefix, $loader);

        return $loader;
    }

    /**
     * resolveLoader
     *
     * @param string $prefix
     *
     * @return Thapp\Jmg\Loader\LoaderInterface `NULL` if none is found.
     */
    protected function resolveLoader($prefix)
    {
        if (null === $name = $this->getLoaderName($prefix)) {
            return;
        }

        if (method_exists($this, $method = 'create'.ucfirst($name).'Loader')) {
            return call_user_func([$this, $method]);
        } elseif ($this->hasCustomCreator($name)) {
            return $this->callCustomCreator($name, [$this->app]);
        }
    }

    /**
     * getLoaderName
     *
     * @param string $prefix
     *
     * @return string
     */
    protected function getLoaderName($prefix)
    {
        return $this->app['config']["jmg.loaders.{$prefix}"];
    }

    /**
     * createFileLoader
     *
     * @return void
     */
    protected function createFileLoader()
    {
        return new \Thapp\Jmg\Loader\FilesystemLoader;
    }

    /**
     * createHttpLoader
     *
     * @return void
     */
    protected function createHttpLoader()
    {
        $config = $this->app['config']->get('jmg.trusted_sites', []);

        return new \Thapp\Jmg\Loader\HttpLoader($config);
    }

    /**
     * createHttpLoader
     *
     * @return void
     */
    protected function createDropboxLoader()
    {
        throw new \LogicException('Not implemented yet.');

        return new \Thapp\Jmg\Loader\DropboxLoader();
    }
}
