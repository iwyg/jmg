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

use Thapp\Jmg\Parameters;
use Thapp\Jmg\ParamGroup;
use Thapp\Jmg\FilterExpression;

/**
 * @class RecipeResolver
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class RecipeResolver implements RecipeResolverInterface
{
    /** @var array */
    private $recipes;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->set($params);
    }

    /**
     * Resolves a recipe alias to a path alias/ParamGroup pair.
     *
     * @param string $recipe
     *
     * @return array [String, ParamGroup]
     */
    public function resolve($recipe)
    {
        if (!isset($this->recipes[$recipe = trim($recipe, '/')])) {
            return;
        }

        return $this->recipes[$recipe];
    }

    /**
     * set
     *
     * @param array $recipes
     *
     * @return void
     */
    public function set(array $recipes)
    {
        $this->recipes = [];

        foreach ($recipes as $recipe => $values) {
            if (2 !== $count = count($values)) {
                continue;
            }

            if (null === $args = $this->getRecipeArgs($values)) {
                continue;
            }

            list($alias, $params) = $args;

            $this->add($recipe, $alias, $params);
        }
    }

    /**
     * add
     *
     * @param mixed $recipe
     * @param mixed $path
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return void
     */
    public function add($recipe, $alias, ParamGroup $params)
    {
        $this->recipes[trim($recipe, '/')] = [$alias, $params];
    }

    /**
     * getRecipeArgs
     *
     * @param array $values
     *
     * @return array
     */
    private function getRecipeArgs(array $values)
    {
        list ($alias, $param) = $values;

        if (!is_string($alias)) {
            return;
        }

        return $param instanceof ParamGroup ? [$alias, $param] :
            [$alias, ParamGroup::fromString($param ?: '0')];
    }
}
