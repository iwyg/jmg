<?php

/**
 * This File is part of the Thapp\Jmg package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Resolver;

use Thapp\Jmg\ProcessorInterface;
use Thapp\Jmg\Parameters as Params;
use Thapp\Jmg\Resource\ImageResource;
use Thapp\Jmg\Loader\LoaderInterface;
use Thapp\Jmg\FilterExpression as Filters;
use Thapp\Jmg\Validator\ValidatorInterface;
use Thapp\Jmg\Cache\CacheInterface as Cache;

/**
 * @class ImageResolver
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class ImageResolver implements ImageResolverInterface
{
    use ImageResolverHelper;

    /** @var ProcessorInterface */
    private $processor;

    /** @var ResolverInterface */
    private $cacheResolver;

    /** @var PathResolverInterface */
    private $pathResolver;

    /** @var LoaderResolverInterface */
    private $loaderResolver;

    /** @var ValidatorInterface */
    private $constraintValidator;

    /** @var array */
    private $pool;

    /**
     * Create a new ImageResolver instance.
     *
     * @param ProcessorInterface $processor
     * @param PathResolverInterface $pathResolver
     * @param LoaderResolverInterface $loaderResolver
     * @param CacheResolverInterface $cacheResolver
     * @param ValidatorInterface $constraintValidator
     */
    public function __construct(
        ProcessorInterface $processor,
        PathResolverInterface $pathResolver,
        LoaderResolverInterface $loaderResolver,
        CacheResolverInterface $cacheResolver = null,
        ValidatorInterface $constraintValidator = null
    ) {
        $this->processor = $processor;
        $this->pathResolver = $pathResolver;
        $this->loaderResolver = $loaderResolver;
        $this->cacheResolver = $cacheResolver;
        $this->constraintValidator = $constraintValidator;
        $this->pool = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheResolver()
    {
        return $this->cacheResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoaderResolver()
    {
        return $this->loaderResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getPathResolver()
    {
        return $this->pathResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($src, Params $params, Filters $filters = null, $prefix = '')
    {
        if (!isset($this->pool[$pk = $this->poolKey($prefix, $src, $params, $filters)])) {
            $this->pool[$pk] = $this->resolveImage($src, $params, $filters, $prefix);
        }

        return $this->pool[$pk];
    }

    /**
     * Resolve the url parameters to a cached image resource.
     *
     * @param array $params
     *
     * @return ResourceInterface
     */
    public function resolveCached($prefix, $id)
    {
        if (null === $this->cacheResolver) {
            return false;
        }

        if (null === ($cache = $this->cacheResolver->resolve($prefix = trim($prefix, '/')))) {
            return false;
        }

        $pos = strrpos($id, '.');
        $key = strtr($id, ['/' => '.']);

        $key = false !== $pos ? substr($key, 0, $pos) : $key;

        if (!$cache->has($key)) {
            return false;
        }

        return $cache->get($key);
    }

    /**
     * resolveImage
     *
     * @param mixed $src
     * @param Parameters $params
     * @param Filters $filters
     * @param string $prefix
     *
     * @return ResourceInterface
     */
    protected function resolveImage($src, Params $params, Filters $filters = null, $prefix = '')
    {
        $alias = trim($prefix, '/');

        if (!$loader = $this->loaderResolver->resolve($alias)) {
            return false;
        }

        if (null === $path = $this->pathResolver->resolve($alias)) {
            return false;
        }

        $key       = null;
        $cache     = $this->cacheResolver ? $this->cacheResolver->resolve($alias) : null;
        $filterStr = $filters ? (string)$filters : null;

        if (null !== $cache && $cache->has($key = $this->cacheKey($cache, $alias, $src, (string)$params, $filterStr)) &&
            $resource = $cache->get($key)
        ) {
            return $resource;
        }

        if (!$loader->supports($file = $this->getPath($path, $src))) {
            return false;
        }

        $this->validateParams($params);

        if (!$this->loadProcessor($loader, $file)) {
            return false;
        }

        return $this->runProc($file, $params, $filters, $cache, $key);
    }

    /**
     * Validate the url parameters against constraints.
     *
     * @param array $params
     *
     * @throws \OutOfBoundsException if validation fails
     * @return void
     */
    private function validateParams(Params $parameters)
    {
        if (null === $this->constraintValidator) {
            return;
        }

        $params = $parameters->all();

        if (false !== $this->constraintValidator->validate($params['mode'], [$params['width'], $params['height']])) {
            return true;
        }

        throw new \OutOfBoundsException('Parameters exceed limit');
    }

    /**
     * {@inheritdoc}
     */
    private function poolKey($name, $source, Params $params, Filters $filters = null)
    {
        return sprintf('%s/%s:%s%s', $name, $source, (string)$params, $filters ? '/filter:'.($filters) : '');
    }

    /**
     * Return the cache key derived from the url parameters.
     *
     * @param string $path the image source
     * @param string $parameters the parameters as string
     * @param string $filters the filters as string
     *
     * @return string
     */
    private function cacheKey(Cache $cache, $name, $src, $paramStr, $filterStr)
    {
        return $cache->createKey(
            $src,
            $name,
            $paramStr.'/'.$filterStr,
            pathinfo($src, PATHINFO_EXTENSION)
        );
    }

    /**
     * createResource
     *
     * @param ProcessorInterface $processor
     *
     * @return ResourceInterface
     */
    private function createResource(ProcessorInterface $processor)
    {
        list($w, $h) = $processor->getTargetSize();

        $resource = new ImageResource(null, $w, $h, $processor->getColorSpace());

        $resource->setContents($processor->getContents());
        $resource->setFresh(!$processor->isProcessed());
        $resource->setLastModified($processor->getLastModTime());
        $resource->setMimeType($processor->getMimeType());

        // if the image was passed through, we can set a source path
        if (!$processor->isProcessed()) {
            $resource->setPath($processor->getSource());
        }

        return $resource;
    }

    /**
     * loadProcessor
     *
     * @param string $path
     *
     * @return boolean
     */
    private function loadProcessor(LoaderInterface $loader, $path)
    {
        try {
            $resource = $loader->load($path);
            $this->processor->load($resource);
        } catch (SourceLoaderException $e) {
            return false;
        }

        return true;
    }

    /**
     * Run the image processor.
     *
     * @param string $source
     * @param Params $params
     * @param Filters $filters
     * @param Cache $cache
     * @param string $key
     *
     * @return Thapp\Jmg\Resource\ImageResourceInterface
     */
    private function runProc($source, Params $params, Filters $filters = null, Cache $cache = null, $key = null)
    {
        $this->processor->process($params, $filters);

        if (null === $cache) {
            return $this->createResource($this->processor);
        }

        $cache->set($key, $this->processor);

        return $cache->get($key);
    }

    /**
     * getPath
     *
     * @return string
     */
    private function getPath($path, $source)
    {
        if (null === $path || null !== parse_url($source, PHP_URL_SCHEME)) {
            return $source;
        };

        if (null !== parse_url($path, PHP_URL_PATH)) {
            $slash = DIRECTORY_SEPARATOR;

            return rtrim($path, '\\\/') . $slash . strtr($source, ['/' => $slash]);
        }

        return $path . '/' . $source;
    }
}
