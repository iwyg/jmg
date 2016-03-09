<?php

namespace Thapp\Jmg\Tests\Loader;

use phpmock\phpunit\PHPMock;
use Thapp\Jmg\Loader\HttpLoader;

class HttpLoaderTest extends \PHPUnit_Framework_TestCase
{
    use PhpMock;

    private $namespace = 'Thapp\Jmg\Loader';
    private $streams = [];

    /** @test */
    public function itShouldLoadRemoteImageViaCurl()
    {
        $loader = new HttpLoader;

        $file = 'https://example.com/someimage.jpg';

        $exists = $this->getFunctionMock($this->namespace, 'function_exists');
        $exists->expects($this->once())->with('curl_init')->willReturn(true);

        $this->mockCurl($file, ['http_code' => 200]);

        $this->assertInstanceOf('Thapp\Jmg\Resource\FileResourceInterface', $loader->load($file));
    }

    /** @test */
    public function itShouldThrowOnCurlFailure()
    {
        $loader = new HttpLoader;

        $file = 'https://example.com/someimage.jpg';

        $exists = $this->getFunctionMock($this->namespace, 'function_exists');
        $exists->expects($this->once())->with('curl_init')->willReturn(true);

        $this->mockCurl($file, ['http_code' => 404]);

        try {
            $loader->load($file);
        } catch (\Thapp\Jmg\Exception\SourceLoaderException $e) {
            $expected = sprintf('Error loading remote file "%s": resource not found', $file);
            $this->assertEquals($expected, $e->getMessage());
        }
    }

    /** @test */
    public function itShouldThrowOnCurlError()
    {
        $loader = new HttpLoader;

        $file = 'https://example.com/someimage.jpg';

        $exists = $this->getFunctionMock($this->namespace, 'function_exists');
        $exists->expects($this->once())->with('curl_init')->willReturn(true);

        $this->mockCurl($file, ['http_code' => 200], true);

        try {
            $loader->load($file);
        } catch (\Thapp\Jmg\Exception\SourceLoaderException $e) {
            $expected = sprintf('Error loading remote file "%s": stubbed error message', $file);
            $this->assertEquals($expected, $e->getMessage());
        }
    }

    /** @test */
    public function itShouldThrowIfSourceIsNotInTrustedHost()
    {
        $file = 'https://example.com/someimage.jpg';
        $loader = new HttpLoader(['https://google.com']);

        try {
            $loader->load($file);
        } catch (\Thapp\Jmg\Exception\SourceLoaderException $e) {
            $expected = sprintf('Error loading remote file "%s": forbidden host `example.com`', $file);
            $this->assertEquals($expected, $e->getMessage());
        }
    }

    /** @test */
    public function itShouldLoadImageViafopenIfCurlIsAbsent()
    {
        $loader = new HttpLoader;

        $file = 'https://example.com/someimage.jpg';

        $exists = $this->getFunctionMock($this->namespace, 'function_exists');
        $exists->expects($this->once())->with('curl_init')->willReturn(false);

        $fopen = $this->getFunctionMock($this->namespace, 'fopen');
        $fopen->expects($this->once())->with($file, 'r')->willReturnCallback(function (...$args) {
            return $this->getMockedResource();
        });


        $this->assertInstanceOf('Thapp\Jmg\Resource\FileResourceInterface', $loader->load($file));
    }

    protected function tearDown()
    {
        foreach ($this->streams as $stream) {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function mockCurl($url, array $info = [], $triggerError = false)
    {
        $info = array_merge(['http_code' => 404], $info);

        if (!defined('CURLOPT_FILE')) {
            define(CURLOPT_FILE, 3);
        }

        if (!defined('CURLOPT_RETURNTRANSFER')) {
            define(CURLOPT_RETURNTRANSFER, 1);
        }

        if (!defined('CURLOPT_HEADER')) {
            define(CURLOPT_HEADER, 2);
        }

        $stream = null;

        $curl = $this->getMock('curl');
        $cinit = $this->getFunctionMock($this->namespace, 'curl_init');
        $cinit->expects($this->once())->with($url)->willReturn($curl);

        $csetopt = $this->getFunctionMock($this->namespace, 'curl_setopt');
        $csetopt->expects($this->any())->willReturnCallback(function (...$args) use (&$stream, $info) {
            if (in_array($info['http_code'], [200, 302, 304]) && is_resource($args[2])) {
                $stream = $args[2];
            }
        });

        $cclose = $this->getFunctionMock($this->namespace, 'curl_close');
        $cclose->expects($this->once())->with($curl);

        $cinfo = $this->getFunctionMock($this->namespace, 'curl_getinfo');
        $cinfo->expects($this->once())->willReturn($info);

        $cexec = $this->getFunctionMock($this->namespace, 'curl_exec');
        $cexec->expects($this->once())->with($curl)->willReturnCallback(function () use (&$stream) {
            if (is_resource($stream)) {
                $stream = $this->getMockedResource($stream);
                return true;
            }

            return false;
        });

        $cerror = $this->getFunctionMock($this->namespace, 'curl_error');
        $cerror->expects($this->any())->with($curl)->willReturnCallback(function () use ($triggerError) {
            if (!$triggerError) {
                return '';
            }

            return 'stubbed error message';
        });
    }

    protected function getMockedResource($resource = null)
    {
        $streamA = fopen($this->getMockFile(), 'r');
        $streamB = $resource ?: fopen('php://temp', 'wb+');

        stream_copy_to_stream($streamA, $streamB);
        fclose($streamA);
        rewind($streamB);

        return $this->stream[] = $streamB;
    }

    protected function getMockFile()
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixures'.DIRECTORY_SEPARATOR.'pattern.png';
    }
}
