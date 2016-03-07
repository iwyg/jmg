<?php

namespace Thapp\Jmg\Tests\Resolver;

use Thapp\Jmg\Resolver\RecipeResolver;

class RecipeResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceOf('Thapp\Jmg\Resolver\RecipeResolverInterface', new RecipeResolver);
    }

    /** @test */
    public function itShouldResolveRecipes()
    {
        $resolver = new RecipeResolver(
            [
                'foo' => ['images', '0'],
                'bar' => ['images', '2/300/300/5|5/50'],
            ]
        );

        $this->assertTrue(in_array('images', $retA = $resolver->resolve('foo')));
        $this->assertTrue(in_array('images', $retB = $resolver->resolve('bar')));

        $this->assertInstanceof('Thapp\Jmg\ParamGroup', $retA[1]);
        $this->assertInstanceof('Thapp\Jmg\ParamGroup', $retB[1]);
    }
}
