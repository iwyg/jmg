<?php

/*
 * This File is part of the  package
 *
 * (c)  <>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Api;

use Thapp\Jmg\Parameters as Params;
use Thapp\Jmg\FilterExpression as Filters;
use Thapp\Jmg\Resolver\ImageResolverInterface;
use Thapp\Jmg\Resolver\RecipeResolverInterface;
use Thapp\Jmg\Http\UrlBuilderInterface as Url;
use Thapp\Jmg\Resource\ResourceInterface;
use Thapp\Jmg\Resource\CachedResourceInterface;

class Generator
{
    private $resolver;
    private $recipes;
    private $url;
    private $cachePrefix;
    public function __construct(ImageResolverInterface $resolver, RecipeResolverInterface $rec, Url $url, $cpfx)
    {
        $this->resolver = $resolver;
        $this->recipes = $rec;
        $this->url = $url;
        $this->cachePrefix = $cpfx;
    }

    /**
     * fromParams
     *
     * @param mixed $src
     * @param Params $params
     * @param Filters $filters
     * @param string $prefix
     * @param mixed $q
     *
     * @return void
     */
    public function fromParams($src, Params $params, Filters $filters = null, $prefix = '', $q = false)
    {
        if (!$resource = $this->resolve($src, $params, $filters, $prefix)) {
            return [];
        }

        return $this->getParsed($resource, $src, $prefix, $params, $filters, null, $q);
    }

    /**
     * fromRecipe
     *
     * @param mixed $recipe
     * @param mixed $src
     *
     * @return void
     */
    public function fromRecipe($recipe, $src)
    {
        list($prefix, $params, $filters) = $recipe->resolve($recipe);

        if (null === $params) {
            return [];
        }

        if (!$resource = $this->resolve($src, $params, $filters, $prefix)) {
            return [];
        }

        return $this->getParsed($resource, $src, $prefix, $params, $filters, $recipe, $q);
    }

    /**
     * getCachedUri
     *
     * @param CachedResourceInterface $resource
     * @param mixed $src
     * @param mixed $prefix
     *
     * @return void
     */
    private function getCachedUri(CachedResourceInterface $resource, $prefix)
    {
        return $this->url->getCachedUri($resource, $prefix, $this->cachePrefix);
    }

    /**
     * getUri
     *
     * @param mixed $src
     * @param mixed $prefix
     * @param Params $params
     * @param Filters $filter
     * @param mixed $recipe
     * @param mixed $q
     *
     * @return void
     */
    private function getUri($src, $prefix, Params $params, Filters $filter = null, $recipe = null, $q = false)
    {
        $q = null !== $recipe ? (bool)$q : false;
        return null !== $recipe ? $this->url->getRecipeUri($src, $recipe, $params, $filter) :
            $this->url->getUri($src, $params, $filter, $prefix, true);
    }

    /**
     * getParsed
     *
     * @param ResourceInterface $resource
     * @param string $src
     * @param string $prefix
     * @param Params $params
     * @param Filters $filters
     * @param string $recipe
     * @param bool $asQuery
     *
     * @return void
     */
    private function getParsed(
        ResourceInterface $resource,
        $src,
        $prefix,
        Params $params,
        Filters $filters = null,
        $recipe = null,
        $asQuery = false
    ) {
        if ($resource instanceof CachedResourceInterface) {
            $uri = $this->getCachedUri($resource, $prefix);
        } else {
            $uri = $this->geteUri($src, $prefix, $params, $filters, $recipe, $asQuery);
        }

        return [
            'uri'    => $uri,
            'name'   => $src,
            'height' => $resource->getHeight(),
            'width'  => $resource->getWidth(),
            'type'   => $resource->getMimeType()
        ];
    }

    /**
     * resolve
     *
     * @param mixed $src
     * @param Params $params
     * @param Filters $filters
     * @param mixed $prefix
     *
     * @return void
     */
    private function resolve($src, Params $params, Filters $filters = null, $prefix = null)
    {
        if (!$resource = $this->resolver->resolve($src, $params, $filters, $prefix)) {
            return false;
        }

        return $resource;
    }
}
