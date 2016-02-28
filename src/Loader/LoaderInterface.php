<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Loader;

/**
 * @interface LoaderInterface
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface LoaderInterface
{
    /**
     * Loads a resource.
     *
     * @param string|resource $source
     *
     * @return Thapp\Jmg\Resource\FileResourceInterface
     */
    public function load($source);

    /**
     * Reset the current resource.
     *
     * @return void
     */
    public function clean();

    /**
     * Check if the loader supports the resource to be loaded.
     *
     * @param mixed $resource
     *
     * @return boolean
     */
    public function supports($resource);
}
