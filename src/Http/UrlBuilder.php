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

    /**
     * Constructor.
     *
     * @param HttpSignerInterface $signer
     */
    public function __construct(HttpSignerInterface $signer = null)
    {
        $this->signer = $signer;
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
        return sprintf('%s%s%s', $recipe, $separator, trim($src, '/'));
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
