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

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Thapp\Jmg\Resource\ResourceInterface;
use Thapp\Jmg\Resource\CachedResourceInterface;

/**
 * @class ImageResponse
 *
 * @package Thapp\Jmg\Http\PSR7
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ImageResponse implements ResponseInterface
{
    /** @var ResourceInterface */
    private $image;

    /** @var array */
    private $headers;

    /** @var int */
    private $status;

    /** @var string */
    private $reason;

    /** @var string */
    private $version;

    /** @var ImageStream */
    private $stream;

    /** @var ImageStream */
    protected static $allowedHeaders = [
        'accept', 'accept-rages', 'date', 'keep-alive', 'connection', 'content-transfer-encoding',
        'etag', 'content-encoding', 'content-length', 'content-type', 'last-modified'
    ];

    /**
     * Constructor.
     *
     * @param ImageResourceInterface $image
     * @param array $headers
     * @param string $version
     */
    public function __construct(ResourceInterface $image, array $headers = [], $version = '1.1')
    {
        $this->version = $version;
        $this->status  = 200;
        $this->reason  = 'Ok';
        $this->image   = $image;
        $this->setHeaders($this->filterHeaders($headers));
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return $this->reason;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($status, $reasonPhrase = '')
    {
        $response = clone $this;
        $response->status = $status;
        $response->reason = $reasonPhrase;

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->getImageStream();
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        $response = clone $this;
        $response->version = $version;

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return [];
        }

        return $this->headers[strtolower($name)];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        if (!$this->hasHeader($name)) {
            return '';
        }

        return implode(',', $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $response = clone $this;

        if ($this->isAllowedHeader($name = strtolower($name))) {
            $response->headers[$name] = $this->getHeaderValue($value);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $response = clone $this;

        if ($this->isAllowedHeader($name = strtolower($name))) {
            $value = $this->getHeaderValue($value);
            $header = $this->getHeader($name);
            $response->headers[$name] = $header + $value;
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($header)
    {
        $header = strtolower($header);
        $response = clone $this;
        $response->headers = array_filter($this->headers, function ($key) use ($header) {
            return $key !== $header;
        }, ARRAY_FILTER_USE_KEY);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        if (!$body instanceof ImageStream) {
            throw new \RuntimeException;
        }

        $response = clone $this;

        $response->image = null;
        $response->stream = $body;

        return $response;
    }

    /**
     * getDefaultHeader
     *
     * @return array
     */
    protected function getDefaultHeader()
    {
        return ['Content-Transfer-Encoding' => 'binary'];
    }

    /**
     * isSaneHeader
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isAllowedHeader($key)
    {
        return in_array($key, static::$allowedHeaders);
    }

    /**
     * prepareHeaders
     *
     * @param array $headers
     *
     * @return array
     */
    protected function prepareHeaders(array $headers)
    {
        return array_merge($headers, $this->getDefaultHeader(), [
            'content-type'              => $this->image->getMimeType(),
            'accept-ranges'             => 'bytes',
            'keep-alive'                => 'timeout=15, max=200',
            'connection'                => 'Keep-Alive',
        ]);
    }

    private function setHeaders(array $headers = [])
    {
        $this->headers = array_change_key_case(array_map(function ($value) {
            return $this->getHeaderValue($value);
        }, $this->prepareHeaders($headers)), CASE_LOWER);
    }

    /**
     * filterHeaders
     *
     * @param array $headers
     *
     * @return array
     */
    private function filterHeaders(array $headers)
    {
        return array_filter(array_change_key_case($headers), [$this, 'isAllowedHeader'], ARRAY_FILTER_USE_KEY);
    }

    private function getHeaderValue($value)
    {
        return is_string($value) ? (array)$value : (is_array($value) ? $value : []);
    }

    private function getImageStream()
    {
        return null !== $this->stream ? $this->stream : $this->stream = new ImageStream($this->image);
    }
}