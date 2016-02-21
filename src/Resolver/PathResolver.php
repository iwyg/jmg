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

/**
 * @class PathResolver implements ResolverInterface PathResolver
 * @see ResolverInterface
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class PathResolver implements PathResolverInterface
{
    /** @var array */
    private $mappings;

    /**
     * @param array $mapping
     */
    public function __construct(array $mapping = [])
    {
        $this->setMappings($mapping);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->mappings;
    }

    /**
     * Resolve a route path to a file base path.
     *
     * @param string $path
     *
     * @return string the resolved path, `NULL` on error
     */
    public function resolve($path)
    {
        if ($this->hasAlias($alias = trim($path, '/'))) {
            return $this->mappings[$alias];
        }

        return null;
    }

    private function hasAlias($alias)
    {
        return array_key_exists($alias, $this->mappings);
    }

    /**
     * setMappings
     *
     * @param array $mappings
     *
     * @return void
     */
    private function setMappings(array $mappings)
    {
        $this->mappings = [];

        foreach ($mappings as $alias => $path) {
            $this->mappings[trim($alias, '/')] = $path;
        }
    }
}
