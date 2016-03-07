<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\View;

use InvalidArgumentException;
use Thapp\Jmg\ParamGroup;
use Thapp\Jmg\Http\UrlBuilder;
use Thapp\Jmg\Resource\CachedResourceInterface;
use Thapp\Jmg\Http\UrlBuilderInterface as Url;
use Thapp\Jmg\Resolver\ImageResolverInterface as ImageResolver;
use Thapp\Jmg\Resolver\RecipeResolverInterface as Recipes;

/**
 * @class Jmg
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Jmg implements Applyable
{
    /** @var Generator */
    private $generator;

    /** @var ImageResolverInterface */
    private $images;

    /** @var array */
    private $options;

    /** @var bool */
    private $chain;

    /** @var Recipes */
    private $recipes;

    /** @var Url */
    private $url;

    /**
     * Constructor.
     *
     * @param ImageResolver $imageResolver
     * @param RecipeResolverInterface $recipes
     * @param UrlBuilderInterface $url
     */
    public function __construct(ImageResolver $resolver, Recipes $recipes, Url $url = null, array $options = [])
    {
        $this->images  = $resolver;
        $this->recipes = $recipes;
        $this->url     = $url ?: new UrlBuilder;

        $this->setOptions($options);
    }

    /**
     * Get the ImageResolver
     *
     * @return ImageResolver
     */
    public function getImageResolver()
    {
        return $this->images;
    }

    /**
     * Get the RecipesResolver
     *
     * @return RecipesResolverInterface
     */
    public function getRecipesResolver()
    {
        return $this->recipes;
    }

    /**
     * Takes an image source stirng for manipulation.
     *
     * @param string $source the image source path
     * @param string $path the image base path
     *
     * @return Generator
     */
    public function with($source, $path = null, $asTag = false, array $attributes = null, $query = true)
    {
        $path = $path ?: $this->getOption('default_prefix');
        $task = new Task($this->chain, $path, $source, $asTag, $attributes, (bool)$query);
        $this->chain = false;

        return $this->newGenerator($task);
    }

    /**
     * Creates an image path from a given recipe
     *
     * @param string $recipe
     *
     * @return string
     */
    public function make($recipe, $source, $asTag = false, array $attributes = [])
    {
        if (!$recipes = $this->recipes->resolve($recipe)) {
            throw new InvalidArgumentException();
        }

        list ($alias, $params) = $recipes;

        $task = new Task(false, $source, $asTag, $attributes, false);
        $taks->setParams($params);

        return $this->flushTask($task, $recipe);
    }

    /**
     * apply
     *
     * @return string
     */
    public function apply(Task $task)
    {
        if ($task->isChained()) {
            return $this->newGenerator($task);
        }

        return $this->flushTask($task);
    }

    /**
     * Starts a chained process.
     *
     * @return self
     */
    public function chain()
    {
        $this->chain = true;

        return $this;
    }

    /**
     * flushTask
     *
     * @param ParamGroup $params
     * @param string $recipe
     *
     * @return string
     */
    private function flushTask(Task $task, $recipe = null)
    {
        $params = $task->getParams();

        if (!$resource = $this->images->resolve($task->getSource(), $params, $task->getPrefix())) {
            $this->clear();
            return '';
        }

        if ($resource instanceof CachedResourceInterface) {
            $url = $this->url->fromCached($resource, $this->getOption('cache_prefix'));
        } elseif ($this->query) {
            $uri = $this->url->asQuery($task->getPrefix(), $task->getSource(), $params);
        } else {
            $uri = $this->url->fromQuery($task->getPrefix(), $task->getSource(), $params);
        }

        $this->clear();

        return $this->getOutput($task, $uri, $resource);
    }

    /**
     * close
     *
     * @return void
     */
    private function clear()
    {
        $this->images->getProcessor()->close();
    }

    /**
     * getOutput
     *
     * @param string $path
     *
     * @return string
     */
    private function getOutput(Task $task, $path, ImageResourceInterface $resource)
    {
        if ($task->isTag()) {
            return $this->createTag($path, array_merge($task->getAttributes(), $this->getResourceDimension($resource)));
        }

        return $path;
    }

    /**
     * getResourceDimension
     *
     * @return arra
     */
    private function getResourceDimension(ImageResourceInterface $resource)
    {
        return ['width' => $resource->getWidth(), 'height' => $resource->getHeight()];
    }

    /**
     * createTag
     *
     * @param string $path
     * @param array $attributes
     *
     * @return string
     */
    private function createTag($path, array $attributes)
    {
        $parts = '';
        foreach ($attributes as $attribute => $value) {
            $parts .= sprintf('%s="%s" ', $attribute, $value);
        }

        return sprintf('<img src="%s" %s/>', $path, $parts);
    }

    /**
     * newGenerator
     *
     * @return Generator
     */
    private function newGenerator(Task $task)
    {
        if (null === $this->generator) {
            return $this->generator = new Generator($this, $task);
        }

        $gen = clone $this->generator;
        $gen->setTask($task);

        return $gen;
    }

    private function setOptions(array $options)
    {
        $this->options = array_merge(self::defaults(), $options);
    }

    private function getOption($option, $default = null)
    {
        return isset($this->options[$option]) ? $this->options[$option] : $default;
    }

    private static function defaults()
    {
        return [
            'default_prefix'       => 'images',
            'cache_prefix'         => 'cached',
            'url_source_separator' => ':',
        ];
    }
}
