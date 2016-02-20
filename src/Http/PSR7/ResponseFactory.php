<?php

/*
 * This File is part of the Thapp\Jmg\Http\PSR7 package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Http\PSR7;

use DateTime;
use Psr\Http\Message\MessageInterface;
use Thapp\Jmg\Resource\ResourceInterface;
use Thapp\Jmg\Resource\CachedResourceInterface;

/**
 * @class ResponseFactory
 *
 * @package Thapp\Jmg\Http\PSR7
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ResponseFactory
{
    private $useXsend;

    public function __construct($useXsend = false)
    {
        $this->useXsend = (bool)$useXsend;
    }

    public function getResponse(MessageInterface $request, ResourceInterface $resource)
    {
        $version      = $request->getProtocolVersion();
        $time         = time();
        $lastMod      = (new DateTime)->setTimestamp($modDate = $resource->getLastModified());
        $mod          = strtotime($request->getHeaderLine('if-modified-since')) ?: $time;

        $etag = $request->getHeaderLine('if-none-match');
        $resourceEtag = $resource->getHash();

        $headers = [
            'Last-Modified' => $lastMod->format('D, d M Y H:i:s').' GMT',
            'ETag' => $resourceEtag
        ];

        if (0 === strcmp($etag, $resourceEtag) ||
            // not modified response
            (($resource instanceof CachedResourceInterface &&
            $resource->isFresh($time)) && $mod === $modDate)) {
            $response = new NotModifiedImageResonse($resource, [], $version);
            $response = $response->withHeader('last-modified', $headers['Last-Modified']);
        } else {
            // normal response
            $response = new ImageResponse($resource, $headers, $version);
        }

        return $response;
    }
}
