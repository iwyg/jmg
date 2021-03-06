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
 * @class ImageResource extends AbstractResource
 * @see AbstractResource
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class ImageResource extends AbstractResource implements ImageResourceInterface
{
    /** @var int */
    private $width;

    /** @var int */
    private $height;

    /** @var string */
    private $coloespace;

    /**
     * Constructor.
     *
     * @param string $path
     * @param int $width
     * @param int $height
     */
    public function __construct($path = null, $width = null, $height = null, $colorspace = null)
    {
        $this->path       = $path;
        $this->width      = $width;
        $this->height     = $height;
        $this->colorspace = $colorspace;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth()
    {
        if (null !== $this->width) {
            return $this->width;
        }

        return $this->widthFromPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight()
    {
        if (null !== $this->height) {
            return $this->height;
        }

        return $this->heightFromPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getColorSpace()
    {
        return $this->colorspace;
    }

    /**
     * widthFromPath
     *
     * @return int
     */
    private function widthFromPath()
    {
        $this->detectSize();

        return $this->width;
    }

    /**
     * heightFromPath
     *
     * @return int
     */
    private function heightFromPath()
    {
        $this->detectSize();

        return $this->height;
    }

    /**
     * detectSize
     *
     * @return void
     */
    private function detectSize()
    {
        if (null !== $this->getPath() && $this->isLocal()) {
            $size = getimagesize($this->getPath());
        } else {
            $size = getimagesizefromstring($this->getContents());
        }

        $this->width  = $size[0];
        $this->height = $size[1];
    }
}
