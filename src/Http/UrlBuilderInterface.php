<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Http;

use Thapp\Jmg\ParamGroup;
use Thapp\Jmg\Resource\CachedResourceInterface;

/**
 * @interface UrlBuilderInterface
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface UrlBuilderInterface
{
    /**
     * Creates an uri based on a prefix
     *
     * @param string  $prefix    prefix of `$src`.
     * @param string  $src       the image source
     * @param Params  $params    the parameter object
     * @param Filters $filters   the filter objects
     * @param string  $separator the separator between `$prefix` and `$src`
     *
     * @return string
     */
    public function withParams($prefix, $src, ParamGroup $params, $separator = ':');

    /**
     * Creates an URI assuming a jmg query.
     *
     * @param string  $prefix    prefix of `$src`.
     * @param string  $src       the image source
     * @param array   $params    an array of [[Params, Filters]]
     * @param string  $separator the separator between `$prefix` and `$src`
     *
     * @return void
     */
    public function asQuery($prefix, $src, ParamGroup $params, $separator = ':');

    /**
     * fromRecipe
     *
     * @param string $src
     * @param string $recipe
     * @param string $separator
     *
     * @return string
     */
    public function fromRecipe($recipe, $src, $separator = ':');

    /**
     * fromCached
     *
     * @param CachedResourceInterface $resource
     * @param mixed $path
     * @param mixed $prefix
     *
     * @return string
     */
    public function fromCached(CachedResourceInterface $resource, $path, $prefix);
}
