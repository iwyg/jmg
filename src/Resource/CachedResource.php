<?php

/**
 * This File is part of the Thapp\Jmg package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Resource;

use Thapp\Jmg\ProcessorInterface;

/**
 * @class CachedResource extends AbstractResource
 * @see AbstractResource
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
class CachedResource extends AbstractResource implements CachedResourceInterface
{
    protected $key;
    protected $hash;
    protected $width;
    protected $height;

    /**
     * @param string $path
     * @param string $contents
     * @param int $lastModified
     * @param string $mime
     */
    public function __construct(ProcessorInterface $proc, $key, $path = null)
    {
        $this->key = $key;
        $this->path = $path;
        $this->contents = $proc->getContents();
        $this->hash = parent::getHash();

        $this->mimeType     = $proc->getMimeType();
        $this->lastModified = $proc->getLastModTime();
        $this->fresh = false;

        $this->setSize($proc);
    }

    public function getHash()
    {
        return $this->hash;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName()
    {
        return null !== $this->path ? basename($this->path) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return $this->contents = is_callable($this->contents) ? call_user_func($this->contents) : $this->contents;
    }

    /**
     * {@inheritdoc}
     */
    public function setContents($contents)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLastModified($time)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setMimeType($type)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * serialize
     *
     * @return string
     */
    public function serialize()
    {
        return serialize([
         'key'          => $this->key,
         'path'         => $this->path,
         'mimeType'     => $this->mimeType,
         'hash'         => $this->hash,
         'lastModified' => $this->lastModified,
         'width'        => $this->width,
         'height'       => $this->height
        ]);
    }

    /**
     * unserialize
     *
     * @param string $data
     *
     * @return CachedResource
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->key          = $data['key'];
        $this->path         = $data['path'];
        $this->mimeType     = $data['mimeType'];
        $this->hash         = $data['hash'];
        $this->lastModified = $data['lastModified'];
        $this->width        = $data['width'];
        $this->height       = $data['height'];

        $this->contents = $this->initContent();
    }

    /**
     * initContent
     *
     * @return \Closure
     */
    protected function initContent()
    {
        return function () {
            return file_get_contents($this->path);
        };
    }

    /**
     * setSize
     *
     * @param ProcessorInterface $proc
     *
     * @return void
     */
    protected function setSize(ProcessorInterface $proc)
    {
        list ($w, $h) = array_values($proc->getTargetSize());

        $this->width  = $w;
        $this->height = $h;
    }
}
