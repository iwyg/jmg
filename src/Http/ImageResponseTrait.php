<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Http;

use Thapp\Image\Resource\ResourceInterface;

/**
 * @trait ImageResponseTrait
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
trait ImageResponseTrait
{
    /** @var ResourceInterface */
    private $resource;

    /**
     * initialize
     *
     * @param ResourceInterface $image
     *
     * @return void
     */
    private function initialize(ResourceInterface $image)
    {
        $this->resource = $image;
    }

    /**
     * Set XsendFile Headers
     *
     * @return void
     */
    private function setXsendFileHeaders()
    {
    }
}
