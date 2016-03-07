<?php

/*
 * This File is part of the Thapp\Jmg\Tests\Http package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Http;

use Thapp\Jmg\ParamGroup;
use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;
use Thapp\Jmg\Http\UrlBuilder;

/**
 * @class UrlBuilderTest
 *
 * @package Thapp\Jmg\Tests\Http
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class UrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\Http\UrlBuilderInterface', new UrlBuilder);
    }

    /**
     * @test
     * @dataProvider getUriProvider
     */
    public function itShouldCreateParameterUri($expected, $src, $pstr, $fstr, $prefix)
    {
        $signer = $this->mockSigner();
        $signer->expects($this->once())->method('sign')->willReturnCallback(function ($path) {
            return $path;
        });

        $builder = new UrlBuilder($signer);
        $group = new ParamGroup;
        $group->add(Parameters::fromString($pstr), new FilterExpression($fstr ?: ''));
        $uri = $builder->withParams($prefix, $src, $group);

        $this->assertEquals($expected, $uri);
    }

    /**
     * @test
     * @dataProvider getQueryProvider
     */
    public function itShouldCreateQuery($expected, $prefix, $src, ParamGroup $params)
    {
        $signer = $this->mockSigner();
        $signer->expects($this->once())->method('sign')->willReturnCallback(function ($path) {
            return $path;
        });

        $builder = new UrlBuilder($signer);
        $uri = $builder->asQuery($prefix, $src, $params);

        $this->assertEquals($expected, rawurldecode($uri));
    }

    /** @test */
    public function itShouldThrowOnBuildingRecipesUriIfNoResolverIsSet()
    {
        $builder = new UrlBuilder();
        try {
            $builder->fromRecipe('foo', 'image.jpg');
        } catch (\LogicException $e) {
            $this->assertEquals('Can\'t build uri for recipes without resolver.', $e->getMessage());
        }
    }

    /** @test */
    public function itShouldThrowOnBuildingRecipesUriIfNoRecipeCanBeResolved()
    {
        $builder = new UrlBuilder(null, $rec = $this->mockRecipes());
        $rec->method('resolve')->willReturn(null);
        try {
            $builder->fromRecipe('foo', 'image.jpg');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Can\'t build uri for recipe "foo".', $e->getMessage());
        }
    }

    /** @test */
    public function itShouldResolveRecipes()
    {
        $signer = $this->mockSigner();
        $signer->expects($this->exactly(2))->method('sign')->willReturnCallback(function ($path) {
            return $path;
        });
        $rec = $this->mockRecipes();
        $rec->method('resolve')->with('foo')->willReturn(['images', $this->mockParamGroup()]);

        $builder = new UrlBuilder($signer, $rec);

        $this->assertSame('foo:image.jpg', $builder->fromRecipe('foo', 'image.jpg'));
        $this->assertSame('foo/image.jpg', $builder->fromRecipe('foo', 'image.jpg', '/'));
    }

    public function getQueryProvider()
    {
        return [
            [
                'images:image.jpg?jmg[0]=1:100:0:filter:gray;c=1',
                'images', 'image.jpg', ParamGroup::fromString('1/100/0/filter:gray;c=1')
            ],

            [
                'images:image.jpg?jmg[0]=1:100:0',
                'images', 'image.jpg', ParamGroup::fromString('1/100/0')
            ],

            [
                'images:image.jpg?jmg[0]=1:600:0:filter:gray;c=1&jmg[1]=2:200:200:5:filter:circle',
                'images', 'image.jpg', ParamGroup::fromString('1/600/0/filter:gray;c=1|2/200/200/5/filter:circle')
            ],
        ];
    }

    public function getUriProvider()
    {
        return [
            ['/images/0/image.jpg', 'image.jpg', '0', null, 'images'],
            ['/thumbs/2/400/400/5/image.jpg', 'image.jpg', '2/400/400/5', null, 'thumbs'],
            ['/images/2/400/400/5/filter:gray;c=1/image.jpg', 'image.jpg', '2/400/400/5', 'gray;c=1', 'images'],
        ];
    }

    public function getUriSignProvider()
    {
        return [
            ['/images/0/image.jpg', 'image.jpg', '0', null, 'images', '?token=my_token']
        ];
    }

    protected function mockSigner()
    {
        return $this->getMockBuilder('Thapp\Jmg\Http\HttpSignerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function mockParamGroup()
    {
        return $this->getMockBuilder('Thapp\Jmg\ParamGroup')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function mockRecipes()
    {
        return $this->getMockBuilder('Thapp\Jmg\Resolver\RecipeResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
