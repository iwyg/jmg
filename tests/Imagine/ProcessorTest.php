<?php

/*
 * This File is part of the Thapp\Jmg\Tests\Imagine package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Tests\Imagine;

use Thapp\Jmg\Parameters;
use Thapp\Jmg\Imagine\Processor;
use Thapp\Jmg\Tests\ProcessorTest as AbstractProcessorTest;

/**
 * @class ProcessorTest
 *
 * @package Thapp\Jmg\Tests\Imagine
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ProcessorTest extends AbstractProcessorTest
{
    protected $source;

    /** @test */
    public function itShouldBeInstantiable()
    {
        $this->assertInstanceof('Thapp\Jmg\ProcessorInterface', $this->newProcessor());
    }

    /** @test */
    public function itShouldLoadAResource()
    {
        $proc = $this->newProcessor();
        $this->source->expects($this->once())->method('read')->willReturn($this->mockDriver());
        $proc->load($this->mockFileresource());

        $this->assertInstanceof('Imagine\Image\ImageInterface', $proc->getDriver());
    }

    protected function newProcessor()
    {
        return new Processor($this->source = $this->mockImagine());
        return $this->getMock('Imagine\Image\ImagineInterface');
    }

    protected function mockDriver()
    {
        return $this->getMock('Imagine\Image\ImageInterface');
    }

    protected function mockImagine()
    {
        return $this->getMock('Imagine\Image\ImagineInterface');
    }

    protected function prepareLoaded()
    {
        $proc = $this->newProcessor();
        $this->source->expects($this->once())->method('read')->willReturn($image = $this->mockDriver());
        $proc->load($resource = $this->mockFileresource());
        $edit = null;

        return [$proc, $image, $resource, $edit];
    }
}
