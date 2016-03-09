<?php

/*
 * This File is part of the Thapp\Jmg\Tests\Image package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Image;

use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;
use Thapp\Jmg\Image\Processor;
use Thapp\Image\Geometry\Size;
use Thapp\Image\Geometry\Point;
use Thapp\Image\Geometry\Gravity;
use Thapp\Jmg\Tests\ProcessorTest as AbstractProcessorTest;

/**
 * @class ProcessorTest
 *
 * @package Thapp\Jmg\Tests\Image
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ProcessorTest extends AbstractProcessorTest
{
    protected $source;
    protected $image;
    protected $edit;

    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\ProcessorInterface', $this->newProcessor());
    }

    /** @test */
    public function itShouldSetOptions()
    {
        $options = ['foo' => 'bar'];
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();
        $proc->setOptions($options);
        $proc->setOption('foo', 'baz');

        $image->expects($this->once())->method('getBlob')->with(null, ['foo' => 'baz']);

        $proc->getContents();
    }

    /** @test */
    public function itShouldGetSourceFormat()
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $this->assertNull($proc->getSourceFormat());

        $resource->expects($this->once())->method('getMimeType')->willReturn('application/octet-stream');

        $this->assertNull($proc->getSourceFormat());

        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $resource->expects($this->once())->method('getMimeType')->willReturn('image/jpeg');
        $this->assertSame('jpg', $proc->getSourceFormat());
    }

    /** @test */
    public function itShouldGetTargetFormat()
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();
        $image->expects($this->once())->method('getFormat')->willReturn('png');

        $this->assertSame('png', $proc->getFileFormat());
        $this->assertSame('png', $proc->getFileExtension());

        $proc->setFileFormat('jpeg');
        $this->assertSame('jpg', $proc->getFileFormat());
        $this->assertSame('jpg', $proc->getFileExtension());
    }

    /** @test */
    public function itShouldLoadAResource()
    {
        $proc = $this->newProcessor();
        $this->source->expects($this->once())->method('read')->willReturn($this->mockDriver());
        $proc->load($this->mockFileresource());

        $this->assertInstanceof('Thapp\Image\Driver\ImageInterface', $proc->getDriver());
    }

    /** @test */
    public function itShouldGetTargetSize()
    {
        list($proc, $image, $resource, ) = $this->prepareLoaded();

        $image->expects($this->once())->method('getWidth')->willReturn(100);
        $image->expects($this->once())->method('getHeight')->willReturn(200);

        $this->assertSame([100, 200], $proc->getTargetSize());
    }

    /**
     * @test
     * @dataProvider resizeParamProvider
     */
    public function itShouldResizeImage($w, $h, $params, $tw, $th)
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $image->expects($this->any())->method('getSize')->willReturn(new Size($w, $h));
        $times = (0 !== $tw || 0 !== $th) ? 1 : 0;

        $edit->expects($this->exactly($times))->method('resize')->will($this->returnCallback(
            function () use ($tw, $th) {
                list($size, ) = func_get_args();
                $this->assertSame($tw, $size->getWidth());
                $this->assertSame($th, $size->getHeight());
            }
        ));

        try {
            $proc->process(Parameters::fromString($params));
        } catch (\LogicException $e) {
            $this->assertTrue(0 === $tw &&  0 === $th);
            return;
        }

        $this->assertTrue($proc->isProcessed());
    }

    /**
     * @test
     * @dataProvider scaleCropParamProvider
     */
    public function itShouldScaleAndCropImage($w, $h, $params, $tw, $th)
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $image->expects($this->any())->method('getSize')->willReturn(new Size($w, $h));
        $times = (0 !== $tw || 0 !== $th) ? 1 : 0;

        $edit->expects($this->exactly(1))->method('crop');

        list(, , , $gravity) = explode('/', $params);

        $image->expects($this->once())->method('setGravity')->will($this->returnCallBack(function ($g) use ($gravity) {
            $this->assertSame($g->getMode(), (int)$gravity);
        }));

        try {
            $proc->process(Parameters::fromString($params));
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(0 === $tw &&  0 === $th);
            return;
        }

        $this->assertTrue($proc->isProcessed());
        $this->assertSame(time(), $proc->getLastModTime());
    }

    /**
     * @test
     * @dataProvider cropParamProvider
     */
    public function itShouldCropImage($w, $h, $params, $tw, $th)
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $image->expects($this->any())->method('getSize')->willReturn(new Size($w, $h));
        $times = (0 !== $tw || 0 !== $th) ? 1 : 0;

        list(, , , $gravity, $color) = array_pad(explode('/', $params), 5, null);

        if (null !== $color) {
            $image->expects($this->once())->method('getPalette')->willreturn(
                $p = $this->getMock('FakePalette', ['getColor'])
            );
        }

        try {
            $proc->process(Parameters::fromString($params));
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(0 === $tw &&  0 === $th);
            return;
        }
    }

    /** @test */
    public function itShouldPassColorToCrop()
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $image->expects($this->any())->method('getSize')->willReturn(new Size(100, 100));

        $image->expects($this->once())->method('getPalette')->willreturn(
            $p = $this->getMock('FakePalette', ['getColor'])
        );

        $p->method('getColor')->willreturn($color = $this->getMock('Thapp\Image\Color\ColorInterface'));

        $edit->expects($this->exactly(1))->method('crop')->will($this->returnCallback(function () use ($color) {
            $args = func_get_args();
            $c = array_pop($args);

            $this->assertSame($color, $c);
        }));

        $proc->process(Parameters::fromString('3/100/100/5/#ff00ff'));
    }

    /**
     * @test
     * @dataProvider fitParamProvider
     */
    public function itShouldResizeAndFit($w, $h, $params, $tw, $th)
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $image->expects($this->any())->method('getSize')->willReturn(new Size($w, $h));
        $edit->expects($this->exactly(1))->method('resize');

        $proc->process(Parameters::fromString($params));
    }

    /**
     * @test
     * @dataProvider percScaleParamProvider
     */
    public function itShouldPercentualScale($w, $h, $params, $tw, $th)
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $image->expects($this->any())->method('getSize')->willReturn(new Size($w, $h));
        $edit->expects($this->exactly(1))->method('resize')->will($this->returnCallback(function () use ($tw, $th) {
            list($size, ) = func_get_args();
            $this->assertSame($tw, $size->getWidth());
            $this->assertSame($th, $size->getHeight());
        }));

        $proc->process(Parameters::fromString($params));
    }

    /**
     * @test
     * @dataProvider pxScaleParamProvider
     */
    public function itShouldPixelScale($w, $h, $params, $px)
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $image->expects($this->any())->method('getSize')->willReturn(new Size($w, $h));
        $edit->expects($this->exactly(1))->method('resize')->will($this->returnCallback(function () use ($w, $h, $px) {
            list($size, ) = func_get_args();

            $pixel = (new Size($w, $h))->pixel($px);

            $this->assertSame($pixel->getWidth(), $size->getWidth());
            $this->assertSame($pixel->getHeight(), $size->getHeight());
        }));

        $proc->process(Parameters::fromString($params));
    }

    /**
     * @test
     * @dataProvider framesParamProvider
     */
    public function itShouldResizeAndCropEachImageFrame($params, $method)
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $image->expects($this->any(1))->method('getSize')->willReturn(new Size(100, 100));
        $image->method('hasFrames')->willReturn(true);
        $image->method('frames')->willReturn($frames = $this->mockFrames());

        $frames->method('count')->willReturn(3);
        $frames->method('coalesce')->willReturn([$image, $image, $image]);

        $edit->expects($this->exactly(3))->method($method);

        $proc->process(Parameters::fromString($params));
    }

    /** @test */
    public function itShouldGetImageContents()
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $image->expects($this->once())->method('getBlob')->willReturn('content');
        $proc->getContents();
    }


    /** @test */
    public function itShouldGetSourcePath()
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();
        $resource->expects($this->exactly(1))->method('getPath')->willReturn('image.jpg');

        $this->assertSame('image.jpg', $proc->getSource());
    }

    /** @test */
    public function itShouldGetResourceModTimeIfNotProcessed()
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();
        $resource->expects($this->exactly(1))->method('getLastModified')->willReturn($time = time() - 1000);

        $this->assertSame($time, $proc->getLastModTime());
    }

    /** @test */
    public function itShouldBeProcessedOnModeZero()
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $proc->process(Parameters::fromString('0'));

        $this->assertFalse($proc->isProcessed());
    }

    /** @test */
    public function itShouldGetImageFormat()
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();

        $image->expects($this->exactly(1))->method('getFormat')->willReturn('png');
        $resource->method('getMimeType')->willReturn('image/jpeg');

        $this->assertSame('image/png', $proc->getMimeType());
        $this->assertSame('image/jpeg', $proc->getSourceMimeType());
        $this->assertSame('jpg', $proc->getSourceFormat());
        $this->assertSame('png', $proc->getFileFormat());
    }

    /** @test */
    public function itShouldProcessFilters()
    {
        $filter = $this->mockFilter();

        $fresolver = $this->mockFilterResolver();
        $fresolver->expects($this->any())->method('resolve')->with('gray')
            ->will($this->returnValueMap([
                ['circle', null],
                ['gray', [$filter, $filter, $filter]]
            ]));
        list($proc, $image, $resource, $edit) = $this->prepareLoaded($fresolver);

        $map = [true, false, false];

        $filter->expects($this->exactly(3))->method('supports')
            ->with($proc)->willreturnCallback(function ($p) use (&$map) {
                return array_pop($map);
            });

        $filter->expects($this->once())->method('apply')->with($proc);

        $proc->process(Parameters::fromString('0'), new FilterExpression('gray'));
    }

    /** @test */
    public function itShouldThrowIfNoSuitableFilterWasSupplied()
    {
        $filter = $this->mockFilter();
        $fresolver = $this->mockFilterResolver();
        $fresolver->expects($this->any())->method('resolve')->with('gray')
            ->will($this->returnValueMap([
                ['circle', null],
                ['gray', [$filter]]
            ]));

        list($proc, $image, $resource, $edit) = $this->prepareLoaded($fresolver);

        $filter->expects($this->once())->method('supports')->with($proc)->willreturn(false);

        try {
            $proc->process(Parameters::fromString('0'), new FilterExpression('gray'));
        } catch (\RuntimeException $e) {
            $this->assertEquals('No suitable filter found.', $e->getMessage());
        }
    }

    /** @test */
    public function itShouldThrowIfFilterWasNotFound()
    {
        $fresolver = $this->mockFilterResolver();
        $fresolver->expects($this->any())->method('resolve')->with('gray')->willReturn(null);

        list($proc, $image, $resource, $edit) = $this->prepareLoaded($fresolver);

        try {
            $proc->process(Parameters::fromString('0'), new FilterExpression('gray'));
        } catch (\RuntimeException $e) {
            $this->assertEquals('Filter "gray" not found.', $e->getMessage());
        }
    }

    /** @test */
    public function itShouldNotLoadFiltersIfResolverIsNull()
    {
        list($proc, $image, $resource, $edit) = $this->prepareLoaded();
        $proc->process(Parameters::fromString('0'), new FilterExpression('gray'));
    }

    public function resizeParamProvider()
    {
        return [
            [100, 200, '1/500/0', 500, 1000],
            [100, 200, '1/0/500', 250, 500],
            [100, 100, '1/500/0', 500, 500],
            [100, 100, '1/500/600', 500, 600],
            [100, 100, '1/0/0', 0, 0],
        ];
    }

    public function scaleCropParamProvider()
    {
        return [
            [100, 200, '2/100/100/5', 100, 100],
            [50, 50, '2/100/100/5', 100, 100]
        ];
    }

    public function cropParamProvider()
    {
        return [
            [100, 200, '3/100/100/5', 100, 100],
            [100, 200, '3/100/100/5/fff', 100, 100]
        ];
    }

    public function fitParamProvider()
    {
        return [
            [100, 200, '4/200/100', 100, 100]
        ];
    }

    public function percScaleParamProvider()
    {
        return [
            [100, 200, '5/200', 200, 400]
        ];
    }

    public function pxScaleParamProvider()
    {
        return [
            [100, 200, '6/100000', 100000]
        ];
    }

    public function framesParamProvider()
    {
        return [
            ['3/50/50/5', 'crop'],
            ['1/50/0', 'resize'],
        ];
    }

    protected function mockSize($w = 100, $h = 100)
    {
        return $this->getMockBuilder('Thapp\Image\Geometry\Size')
            ->setConstructorArgs([$w, $h])
            ->getMock();
    }

    protected function prepareLoaded($filterResolver = null)
    {
        $proc = $this->newProcessor($filterResolver);
        $this->source->expects($this->once())->method('read')->willReturn($image = $this->mockDriver());
        $proc->load($resource = $this->mockFileresource());
        $edit = $this->getMockBuilder('Thapp\Image\Driver\EditInterface')
            ->disableOriginalConstructor()->getMock();

        $image->expects($this->any())->method('edit')->willReturn($edit);

        return [$proc, $image, $resource, $edit];
    }

    protected function newProcessor($resolver = null)
    {
        return new Processor($this->source = $this->mockSource(), $resolver);
    }

    protected function mockFilterResolver()
    {
        return $this->getMockBuilder('Thapp\Jmg\Resolver\FilterResolverInterface')
            ->disableOriginalConstructor()->getMock();
    }
    protected function mockSource()
    {
        return $this->getMockBuilder('Thapp\Image\Driver\SourceInterface')
            ->disableOriginalConstructor()->getMock();
    }

    protected function mockDriver()
    {
        return $this->getMockBuilder('Thapp\Image\Driver\ImageInterface')
            ->disableOriginalConstructor()->getMock();
    }

    protected function mockFrames()
    {
        return $this->getMockBuilder('Thapp\Image\Driver\FramesInterface')
            ->disableOriginalConstructor()->getMock();
    }

    protected function getDriverClass()
    {
        return 'Thapp\Image\Driver\ImageInterface';
    }

    protected function mockFilter()
    {
        return $this->getMockbuilder('Thapp\Jmg\Filter\FilterInterface')
            ->disableOriginalConstructor()->getMock();
    }

    protected function mockParams()
    {
        return $this->getMockbuilder('Thapp\Jmg\Parameters')
            ->disableOriginalConstructor()->getMock();
    }
}
