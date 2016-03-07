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

use Thapp\Jmg\ParamGroup;
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
    public function sign($path, ParamGroup $params)
    {
        $prefix = false !== mb_strpos($path, '?') ? '&' : '?';
        $uri = parse_url($path, PHP_URL_PATH);

        return $path.$prefix.http_build_query([$this->qkey => $this->createSignature($uri, $params)]);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($uri, ParamGroup $params)
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
        if (0 !== strcmp($qparams[$qkey], $this->createSignature($parts['path'], $params))) {
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
    protected function createSignature($path, ParamGroup $params)
    {
        return hash(
            'sha1',
            sprintf('%s:%s%s', $this->key, trim($path, '/'), (string)$params)
        );
    }
}
