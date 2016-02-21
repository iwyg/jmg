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

use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;
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
        $this->signer  = $signer;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri($source, Parameters $params, FilterExpression $filters = null, $prefix = '', $q = false)
    {
        $path = $this->createImageUri($source, $params, $filters, $prefix, $q);

        if (null !== $this->signer) {
            return $this->signer->sign($path, $params, $filters);
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipeUri($source, $recipe, Parameters $params, FilterExpression $filters = null)
    {
        $path = $this->createRecipeUri($recipe, $source);

        if (null !== $this->signer) {
            return $this->signer->sign($path, $params, $filters);
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedUri(CachedResourceInterface $resource, $name, $prefix)
    {
        $basePath = strtr($resource->getKey(), ['.' => '/']);

        return sprintf(
            '/%s/%s/%s%s',
            $prefix,
            $name,
            $this->getCachedPathBasePath($resource),
            $this->getCachedPathExtension($resource)
        );
    }

    /**
     * createImageUri
     *
     * @param Parameters $params
     * @param FilterExpression $filters
     * @param string $prefix
     *
     * @return string
     */
    protected function createImageUri($src, Parameters $params, FilterExpression $filters = null, $pfx = '', $q = false)
    {
        $prefix = trim($pfx, '/');

        if ($q) {
            $queryString = http_build_query($params->all());
            $path = sprintf('%s/%s?%s', $prefix, $src, $queryString);
        } else {
            $filterString = $this->getFiltersAsString($filters);
            $path = sprintf('%s/%s/%s', trim($prefix, '/'), (string)$params, $src, $filterString);
        }
        return '/'.$path;
    }

    /**
     * getFiltersAsString
     *
     * @param FilterExpression $filters
     *
     * @return void
     */
    protected function getFiltersAsString(FilterExpression $filters = null)
    {
        if (null !== $filters && 0 < count($filters->all())) {
            return sprintf('/filter:%s', (string)$filter);
        }

        return '';
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
     * createRecipeUri
     *
     * @param string $recipe
     * @param string $source
     *
     * @return string
     */
    protected function createRecipeUri($recipe, $source)
    {
        return '/'.trim($recipe, '/') . '/' . trim($source, '/');
    }
}
