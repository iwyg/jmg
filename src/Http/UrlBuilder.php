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
use Thapp\Jmg\Cache\CacheInterface;
use Thapp\Jmg\Resource\CachedResourceInterface;
use Thapp\Jmg\Resolver\RecipeResolverInterface;

/**
 * @class UrlBuilder
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class UrlBuilder implements UrlBuilderInterface
{
    /** @var HttpSignerInterface */
    private $signer;

    /** @var RecipeResolverInterface */
    private $recipes;

    /**
     * Constructor.
     *
     * @param HttpSignerInterface $signer
     */
    public function __construct(HttpSignerInterface $signer = null, RecipeResolverInterface $recipes = null)
    {
        $this->signer = $signer;
        $this->recipes = $recipes;
    }

    /**
     * {@inheritdoc}
     */
    public function withParams($prefix, $src, ParamGroup $params, $separator = ':')
    {
        return $this->getSigned(
            sprintf('/%s/%s/%s', trim($prefix, '/'), (string)$params, $src),
            $params
        );
    }

    /**
     * {@inheritdoc}
     */
    public function asQuery($prefix, $src, ParamGroup $params, $separator = ':')
    {
        return $this->getSigned(
            sprintf('%s%s%s?%s', $prefix, $separator, $src, $params->toQueryString()),
            $params
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fromRecipe($recipe, $src, $separator = ':')
    {
        if (null === $this->recipes) {
            throw new \LogicException('Can\'t build uri for recipes without resolver.');
        }

        if (!$recipes = $this->recipes->resolve($recipe)) {
            throw new \InvalidArgumentException(sprintf('Can\'t build uri for recipe "%s".', $recipe));
        }

        list ($alias, $params) = $recipes;
        $path = sprintf('%s%s%s', $recipe, $separator, trim($src, '/'));

        if (null !== $this->signer) {
            return $this->signer->sign($path, $params);
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function fromCached(CachedResourceInterface $resource, $path, $prefix)
    {
        $cname = $this->getCachedPathBasePath($resource);
        $cext = $this->getCachedPathExtension($resource);

        return sprintf('/%s/%s/%s%s', $path, $prefix, $cname, $cext);
    }

    /**
     * getCachedPathBasePath
     *
     * @param CachedResourceInterface $resource
     *
     * @return string
     */
    protected function getCachedPathBasePath(CachedResourceInterface $resource)
    {
        return strtr($resource->getKey(), ['.' => '/']);
    }

    /**
     * getCachedPathExtension
     *
     * @param CachedResourceInterface $resource
     *
     * @return string
     */
    protected function getCachedPathExtension(CachedResourceInterface $resource)
    {
        if ($extension = pathinfo($resource->getPath(), PATHINFO_EXTENSION)) {
            return '.'.$extension;
        }

        return '';
    }

    /**
     * getSigned
     *
     * @param mixed $uri
     * @param ParamGroup $params
     *
     * @return void
     */
    protected function getSigned($uri, ParamGroup $params)
    {
        if (null === $this->signer) {
            return $uri;
        }

        return $this->signer->sign($uri, $params);
    }
}
