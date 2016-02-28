<?php

/*
 * This File is part of the Thapp\Jmg\Tests\Cache package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Cache;

use Thapp\Jmg\Cache\ClientCache;
use Thapp\Jmg\Cache\CacheInterface;

/**
 * @class MemcachedCacheTest
 *
 * @package Thapp\Jmg\Tests\Cache
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ClientCacheTest extends \PHPUnit_Framework_TestCase
{

    /** @test */
    public function itShouldBeInstantiable()
    {
        $client = $this->mockClient();
        $client->expects($this->once())->method('get')->with($this->getKey('my-key'))->willReturn([]);
        $cache = new ClientCache($client, 'my-key');

        $this->assertInstanceof('Thapp\Jmg\Cache\CacheInterface', $cache);
    }

    /** @test */
    public function itShouldRetriveItems()
    {
        $rA = $this->mockResource('a.b');
        $rA->method('getContents')->willReturn('image');
        $client = $this->mockClient();
        $client->expects($this->once())->method('get')->with($this->getKey('jitimage'))->willReturn([
            'a' => ['b' => $rA],
        ]);

        $cache = new ClientCache($client, 'jitimage');

        $client->expects($this->exactly(2))->method('has')->with('jitimage_a.b')->willReturn(true);

        $this->assertFalse($cache->has('b.c'));
        $this->assertSame($rA, $cache->get('a.b'));
        $this->assertSame('image', $cache->get('a.b', true));
    }

    /** @test */
    public function itShouldAoutoSaveCache()
    {
        $client = $this->mockClient();
        $client->expects($this->once())->method('get')->with($this->getKey('jitimage'))->willReturn([
            'a' => ['b' => true],
            'b' => ['c' => true],
        ]);

        $cache = new ClientCache($client, 'jitimage');

        $client->method('has')->will($this->returnValueMap(
            [
                ['jitimage_a.b', true],
                ['jitimage_b.c', false]
            ]
        ));

        $this->assertTrue($cache->has('a.b'));
        $this->assertFalse($cache->has('b.c'));

        $saveVals = ['a' => ['b' => true]];
        $client->expects($this->once())->method('set')->with(
            $this->getKey('jitimage'),
            $saveVals,
            CacheInterface::EXPIRY_NONE
        );
        unset($cache);
    }

    /** @test */
    public function itShouldOnlyOutoSaveIfChangesOccurred()
    {
        $client = $this->mockClient();
        $client->expects($this->once())->method('get')->with($this->getKey('jitimage'))->willReturn($saveVals = [
            'a' => ['b' => true],
            'b' => ['c' => true],
        ]);

        $cache = new ClientCache($client, 'jitimage');
        $client->expects($this->exactly(0))->method('set')->with(
            $this->getKey('jitimage'),
            $saveVals,
            CacheInterface::EXPIRY_NONE
        );
        unset($cache);
    }

    /** @test */
    public function itShouldStoreAndRetrieveItems()
    {
        $cache = new ClientCache($client = $this->mockClient(), 'jitimage');

        $proc = $this->mockProc();
        $proc->expects($this->exactly(2))->method('getContents')->willReturn('');
        $client->expects($this->exactly(2))->method('has')->with('jitimage_a.b')->willReturn(true);

        $cache->set('a.b', $proc);

        $this->assertTrue($cache->has('a.b'));
        $this->assertInstanceOf('Thapp\Jmg\Resource\CachedResourceInterface', $cache->get('a.b'));
    }

    protected function mockProc()
    {
        $proc = $this->getMockBuilder('Thapp\Jmg\ProcessorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $proc->method('getTargetSize')->willReturn([200, 200]);

        return $proc;
    }

    protected function mockResource($key)
    {
        $mock = $this->getMockBuilder('Thapp\Jmg\Resource\CacheClientResource')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('getKey')->willReturn($key);

        return $mock;
    }

    protected function mockClient()
    {
        return $this->getMockBuilder('Thapp\Jmg\Cache\Client\ClientInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getKey($prefix = 'jitimage')
    {
        return 'index.'.$prefix.hash('md5', $prefix);
    }
}
