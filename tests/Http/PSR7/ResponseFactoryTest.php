<?php

/**
 * This File is part of the  package
 *
 * (c)  <>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Http\PSR7;

use Thapp\Jmg\Http\PSR7\ResponseFactory;

class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldReturnImageResponse()
    {
        $factory = new ResponseFactory;
        $request = $this->mockRequest();

        $resource = $this->mockImageResource();
        $resource->method('getLastModified')->willReturn(time());
        $resource->method('getHash')->willReturn('somehash');

        $request->method('getHeaderLine')->willReturn('');

        $this->assertInstanceOf(
            'Thapp\Jmg\Http\PSR7\ImageResponse',
            $response = $factory->getResponse($request, $resource)
        );

        var_dump($response->getHeaders());
    }

    /** @test */
    public function itShouldReturnNotModifiedResponse()
    {
        $factory = new ResponseFactory;
        $request = $this->mockRequest();

        $resource = $this->mockImageResource();

        $time = time();

        $resource->method('getLastModified')->willReturn($time);
        $resource->method('getHash')->willReturn($etag = 'some-etag');
        $resource->method('isFresh')->with($time)->willReturn(true);

        $fmt = new \DateTime;
        $fmt->setTimestamp($time);

        $request->method('getHeaderLine')->will($this->returnValueMap([
            ['if-modified-since', $fmt->format('D, d M Y H:i:s') . ' GMT'],
            ['if-none-match', $etag]
        ]));

        $this->assertInstanceOf(
            'Thapp\Jmg\Http\PSR7\NotModifiedImageResonse',
            $factory->getResponse($request, $resource)
        );

        // etag missmatch
        $request = $this->mockRequest();

        $request->method('getHeaderLine')->will($this->returnValueMap([
            ['if-modified-since', $fmt->format('D, d M Y H:i:s') . ' GMT'],
            ['if-none-match', '044dff9ffa']
        ]));

        $this->assertInstanceOf(
            'Thapp\Jmg\Http\PSR7\NotModifiedImageResonse',
            $response = $factory->getResponse($request, $resource)
        );

    }

    /** @test */
    public function itShouldReturnNotModifiedResponseIfEtagMatch()
    {

        $factory = new ResponseFactory;
        $request = $this->mockRequest();

        $resource = $this->mockImageResource();

        $time = time();
        $resource->method('getLastModified')->willReturn($time);
        $resource->method('getHash')->willReturn($etag = 'some-etag');
        $resource->method('isFresh')->with($time)->willReturn(true);

        $fmt = new \DateTime;
        $fmt->setTimestamp($time);

        $request->method('getHeaderLine')->will($this->returnValueMap([
            ['if-modified-since', $fmt->format('D, d M Y H:i:s') . ' GMT'],
            ['if-none-match', $etag]
        ]));

        $this->assertInstanceOf(
            'Thapp\Jmg\Http\PSR7\NotModifiedImageResonse',
            $factory->getResponse($request, $resource)
        );

    }

    private function mockRequest()
    {
        return $this->getMockbuilder('Psr\Http\Message\ServerRequestInterface')
            ->disableOriginalConstructor()->getMock();
    }

    private function mockImageResource()
    {
        return $this->getMockbuilder('Thapp\Jmg\Resource\ResourceInterface')
            ->disableOriginalConstructor()->getMock();
    }

    private function mockCachedResource()
    {
        return $this->getMockbuilder('Thapp\Jmg\Resource\CachedResourceInterface')
            ->disableOriginalConstructor()->getMock();
    }
}
