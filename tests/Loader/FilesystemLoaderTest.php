<?php

namespace Thapp\Jmg\Tests\Loader;

use Thapp\Jmg\Loader\FilesystemLoader;

class FilesystemLoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itShouldGetFileSource()
    {
        $loader = new FilesystemLoader;
        $this->assertNull($loader->getSource());
        $loader->load($path = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixures'.DIRECTORY_SEPARATOR.'pattern.png');

        $this->assertInstanceOf('Thapp\Jmg\Resource\FileResourceInterface', $loader->getSource());
    }
}
