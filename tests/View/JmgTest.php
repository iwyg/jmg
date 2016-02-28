<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\View;

use Thapp\Jmg\View\Jmg;

/**
 * @class JmgTest
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class JmgTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\View\Jmg', new Jmg($this->mockResolver(), $this->mockRecipes()));
    }

    /** @test */
    public function takeShouldReturnGenerator()
    {
        $jmg = new Jmg($this->mockResolver(), $this->mockRecipes());
        $this->assertInstanceof('Thapp\Jmg\View\Generator', $jmg->take('image'));
    }

    protected function mockResolver()
    {
        return $this->getMock('Thapp\Jmg\Resolver\ImageResolverInterface');
    }

    protected function mockRecipes()
    {
        return $this->getMock('Thapp\Jmg\Resolver\RecipeResolverInterface');
    }
}
