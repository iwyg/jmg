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

use Thapp\Jmg\FilterExpression;
use Thapp\Jmg\Resolver\ImageResolver;

/**
 * @class ImageResolverTest
 *
 * @package Thapp\Jmg\Tests\Resolver
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ImageResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $proc;
    protected $caches;
    protected $paths;
    protected $loaders;
    protected $resolver;
    protected $constraints;

    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\Resolver\ImageResolverInterface', $this->newResolver());
    }

    /** @test */
    public function itShouldNotResolveImageIfLoaderFailsToResolve()
    {
        $res = $this->getResolver();
        $this->loaders->expects($this->once())->method('resolve')->with('media')->willReturn(null);
        $this->proc->expects($this->exactly(0))->method('process');

        $this->assertFalse($res->resolve('image.jpg', $this->mockParams('0'), null, 'media'));
    }

    /** @test */
    public function itShouldNotResolveImageIfPathFailsToResolve()
    {
        $res = $this->getResolver();
        $this->loaders->expects($this->once())->method('resolve')
            ->with('media')->willReturn($loader = $this->mockLoader());
        $loader->expects($this->exactly(0))->method('supports');
        $this->paths->expects($this->once())->method('resolve')->willReturn(null);
        $this->proc->expects($this->exactly(0))->method('process');

        $this->assertFalse($res->resolve('image.jpg', $this->mockParams('0'), null, 'media'));
    }

    /** @test */
    public function itShouldResolveImageWithoutCache()
    {
        $res = $this->getResolver();
        $this->loaders->expects($this->once())->method('resolve')->willReturn($loader = $this->mockLoader());
        $loader->expects($this->any())->method('supports')->with('path/image.jpg')->willReturn(true);
        $this->paths->expects($this->once())->method('resolve')->willReturn('path');

        $this->proc->expects($this->once())->method('process');

        $res->resolve('image.jpg', $this->mockParams('0'), null, 'media');
    }

    /** @test */
    public function itShouldResolveChainedParams()
    {
        $res = $this->getResolver();
        $this->loaders->expects($this->once())->method('resolve')->willReturn($loader = $this->mockLoader());
        $loader->expects($this->any())->method('supports')->with('path/image.jpg')->willReturn(true);

        $this->paths->expects($this->once())->method('resolve')->willReturn('path');
        $this->proc->expects($this->exactly(2))->method('process');

        $params = [
            [$a = $this->mockParams('1/200/0')],
            [$b = $this->mockParams('2/100/100/5'), $this->mockFilter('color;q=1;c=fff')],
        ];

        $res->resolveChained('image.jpg', $params, 'path');
    }

    /** @test */
    public function itShouldResolveImageWithCacheButEmpty()
    {
        $res = $this->newResolver(true);
        $this->caches->expects($this->once())->method('resolve')->willReturn($cache = $this->mockCache());
        $this->withCache($cache, false);

        $this->proc->expects($this->once())->method('process');

        $res->resolve('image.jpg', $this->mockParams('0'), null, 'media');
    }

    /** @test */
    public function itShouldResolveImageIfConstraitsValidate()
    {
        $res = $this->newResolver(true, true);
        $this->constraints->expects($this->once())->method('validate')->willReturn(null);
        $this->caches->expects($this->once())->method('resolve')->willReturn($cache = $this->mockCache());
        $this->withCache($cache, false);

        $this->proc->expects($this->once())->method('process');

        $res->resolve('image.jpg', $this->mockParams('0'), null, 'media');
    }

    /** @test */
    public function itShouldNotResolveImageIfConstraitsFail()
    {
        $res = $this->getResolver(true, true);
        $this->caches->expects($this->once())->method('resolve')->willReturn($cache = $this->mockCache());
        $this->withCache($cache, false);
        $this->constraints->expects($this->once())->method('validate')->willReturn(false);

        $this->proc->expects($this->exactly(0))->method('process');

        try {
            $res->resolve('image.jpg', $this->mockParams('0'), null, 'media');
        } catch (\OutOfBoundsException $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail();
    }

    /** @test */
    public function itShouldResolveImageWithCacheButHot()
    {
        $res = $this->getResolver(true);
        $this->caches->expects($this->once())->method('resolve')->willReturn($cache = $this->mockCache());
        $this->withCache($cache, true);

        $this->proc->expects($this->exactly(0))->method('process');

        $this->assertInstanceof(
            'Thapp\Jmg\Resource\ResourceInterface',
            $res->resolve('image.jpg', $this->mockParams('0'), null, 'media')
        );
    }

    /** @test */
    public function itShouldNotResolverCachedIfNoCacheResolver()
    {
        $res = $this->getResolver(false);

        $this->assertFalse($res->resolveCached('images', 'prefix.filename.jpg'));
    }

    /** @test */
    public function itShouldNotResolverCachedIfNoCache()
    {
        $res = $this->getResolver(true);

        $this->caches->expects($this->exactly(1))->method('resolve')->willReturn(null);
        $this->assertFalse($res->resolveCached('images', 'prefix.filename.jpg'));
    }

    /** @test */
    public function itShouldNotResolverCachedIfEmpty()
    {
        $res = $this->getResolver(true);
        $this->caches->expects($this->exactly(1))->method('resolve')->willReturn($cache = $this->mockCache());
        $cache->expects($this->exactly(1))->method('has')->willReturn(false);

        $this->assertFalse($res->resolveCached('images', 'prefix.filename.jpg'));
    }

    /** @test */
    public function itShouldResolverCachedIfNotEmpty()
    {
        $res = $this->getResolver(true);
        $this->caches->expects($this->exactly(1))->method('resolve')
            ->with('images')
            ->willReturn($cache = $this->mockCache());
        $cache->expects($this->exactly(1))->method('has')->willReturn(true);

        $cache->expects($this->exactly(1))->method('get')
            ->with('prefix.filename')
            ->willReturn($this->mockResource());

        $this->assertInstanceof(
            'Thapp\Jmg\Resource\ResourceInterface',
            $res->resolveCached('/images', 'prefix.filename.jpg')
        );
    }

    /** @test */
    public function itShouldGetResolvers()
    {
        $res = $this->newResolver(true);

        $this->assertSame($this->proc, $res->getProcessor());
        $this->assertSame($this->paths, $res->getPathResolver());
        $this->assertSame($this->loaders, $res->getLoaderResolver());
        $this->assertSame($this->caches, $res->getCacheResolver());
    }

    protected function withCache($cache, $has = false)
    {
        $this->loaders->expects($this->once())->method('resolve')->willReturn($loader = $this->mockLoader());
        $loader->expects($this->any())->method('supports')->with('path/image.jpg')->willReturn(true);
        $this->paths->expects($this->once())->method('resolve')->willReturn('path');
        $cache->expects($this->once())->method('has')->willReturn($has);

        if ($has) {
            $cache->expects($this->once())->method('get')
                ->willReturn($this->getMock('Thapp\Jmg\Resource\ResourceInterface'));
        }
    }

    protected function getResolver($caches = false, $constraints = false)
    {
        if (null === $this->resolver) {
            $this->resolver = $this->newResolver($caches, $constraints);
        }

        return $this->resolver;
    }

    protected function newResolver($caches = false, $constraints = false)
    {
        return new ImageResolver(
            $this->mockProc(),
            $this->mockPaths(),
            $this->mockLoaders(),
            $this->mockCaches($caches),
            $this->mockConstraints($constraints)
        );
    }

    protected function mockProc()
    {
        return $this->proc = $this->getMock('Thapp\Jmg\ProcessorInterface');
    }

    protected function mockPaths()
    {
        return $this->paths = $this->getMock('Thapp\Jmg\Resolver\PathResolverInterface');
    }

    protected function mockLoaders()
    {
        return $this->loaders = $this->getMock('Thapp\Jmg\Resolver\LoaderResolverInterface');
    }

    protected function mockResource()
    {
        return $this->getMock('Thapp\Jmg\Resource\FileResourceInterface');
    }

    protected function mockLoader()
    {
        $loader = $this->getMock('Thapp\Jmg\Loader\LoaderInterface');
        $loader->method('load')->will($this->returnCallBack(function ($file) use ($loader) {
            if ($loader->supports($file)) {
                return $this->mockResource();
            }

            return null;
        }));

        return $loader;
    }

    protected function mockCaches($caches = false)
    {
        if ($caches) {
            return $this->caches = $this->getMock('Thapp\Jmg\Resolver\CacheResolverInterface');
        }
    }

    protected function mockCache()
    {
        return $this->getMock('Thapp\Jmg\Cache\CacheInterface');
    }

    protected function mockParams($str = '')
    {
        $param = $this->getMockBuilder('Thapp\Jmg\Parameters')
            ->disableOriginalConstructor()
            ->getMock();
        $param->method('__toString')->willReturn($str);

        return $param;
    }

    protected function mockFilter($str = '')
    {
        $filter = $this->getMockBuilder('Thapp\Jmg\FilterExpression')
            ->disableOriginalConstructor()
            ->getMock();
        $filter->method('__toString')->willReturn($str);

        $filter->method('all')->willreturn((new FilterExpression($str))->all());

        return $filter;
    }

    protected function mockConstraints($constraints = false)
    {
        if ($constraints) {
            return $this->constraints = $this->getMock('Thapp\Jmg\Validator\ValidatorInterface');
        }
    }

    protected function trearDown()
    {
        $this->resolver = null;
    }
}
