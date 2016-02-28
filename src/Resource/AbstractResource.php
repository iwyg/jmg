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

/**
 * @abstract class AbstractResource
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
abstract class AbstractResource implements ResourceInterface
{
    /** @var string */
    protected $contents;

    /** @var int */
    protected $lastModified;

    /** @var string */
    protected $mimeType;

    /** @var string */
    protected $path;

    /** @var bool */
    protected $fresh;

    /** @var string */
    protected $hash;

    /**
     * {@inheritdoc}
     */
    public function getHash()
    {
        return null !== $this->contents ? hash('sha1', $this->getContents()) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocal()
    {
        return null !== $this->path && is_file($this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($time = null)
    {
        return $this->getLastModified() < ($time ?: time());
    }

    /**
     * Marks resource as modified.
     *
     * @return void
     */
    public function setFresh($fresh)
    {
        $this->fresh = true;
    }

    /**
     * setContents
     *
     * @param string $contents
     *
     * @return void
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
    }

    /**
     * getContents
     *
     * @return string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * setFileMtime
     *
     * @param int $time
     *
     * @return void
     */
    public function setLastModified($time)
    {
        $this->lastModified = $time;
    }

    /**
     * getFileMtime
     *
     * @return int
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * setMimeType
     *
     * @param string $type
     *
     * @return void
     */
    public function setMimeType($type)
    {
        $this->mimeType = $type;
    }

    /**
     * getMimeTyoe
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * setPath
     *
     * @param string $path
     *
     * @return mixed
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * getPath
     *
     * @return void
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName()
    {
        return basename($this->getPath());
    }
}
