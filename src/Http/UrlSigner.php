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

use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;
use Thapp\Jmg\Exception\InvalidSignatureException;

/**
 * @class UrlSigner
 *
 * @package Thapp\Jmg \Http
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class UrlSigner implements HttpSignerInterface
{
    /** @var string */
    private $key;

    /** @var string */
    private $qkey;

    /**
     * Constructor.
     *
     * @param string $key
     * @param string $qkey
     */
    public function __construct($key, $qkey = 'token')
    {
        $this->key  = $key;
        $this->qkey = $qkey;
    }

    /**
     * {@inheritdoc}
     */
    public function sign($path, Parameters $params, FilterExpression $filters = null)
    {
        $prefix = false !== mb_strpos($path, '?') ? '&' : '?';
        $uri = parse_url($path, PHP_URL_PATH);

        return $path.$prefix.http_build_query([$this->qkey => $this->createSignature($uri, $params, $filters)]);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($uri, Parameters $params, FilterExpression $filters = null)
    {
        $qkey = $this->getQParamKey();
        $parts =  parse_url($uri);

        if (!isset($parts['query'])) {
            throw InvalidSignatureException::missingSignature();
        }

        parse_str($parts['query'], $qparams);

        if (!isset($qparams[$qkey])) {
            throw InvalidSignatureException::missingSignature();
        }

        // dont care about queries
        //$path = preg_replace(sprintf('#(&?%s=[0-9a-z]+)#x', preg_quote($qkey, '#')), null, $uri);
        if (0 !== strcmp($qparams[$qkey], $this->createSignature($parts['path'], $params, $filters))) {
            throw InvalidSignatureException::invalidSignature();
        }

        return true;
    }

    /**
     * Get the query token key.
     *
     * @return string
     */
    protected function getQParamKey()
    {
        return $this->qkey;
    }

    /**
     * createSignature
     *
     * @param mixed $path
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return string
     */
    protected function createSignature($path, Parameters $params, FilterExpression $filters = null)
    {
        $filterStr = null !== $filters && 0 < count($filters->all()) ? (string)$filters : '';

        return hash(
            'sha1',
            sprintf('%s:%s%sfilter:%s', $this->key, trim($path, '/'), (string)$params, $filterStr)
        );
    }
}
