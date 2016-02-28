<?php

/*
 * This File is part of the Thapp\Jmg\Tests\Resolver package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Resolver;

use Thapp\Jmg\Resolver\RecipeResolver;

/**
 * @class RecipesResolverTest
 *
 * @package Thapp\Jmg\Tests\Resolver
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class RecipesResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\Resolver\RecipeResolverInterface', new RecipeResolver);
    }

    /** @test */
    public function itShouldSetRecipes()
    {
        $res = new RecipeResolver;

        $res->set(['my_recipe' => ['foo', 'bar']]);
    }
}
