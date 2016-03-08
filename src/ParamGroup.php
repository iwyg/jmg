<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg;

use InvalidArgumentException;

/**
 * @class ParamGroup
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class ParamGroup
{
    /** @var string */
    private $string;

    /** @var array */
    private $params;

    /** @var array */
    private $query;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->set($params);
    }

    /**
     * Sets the parameters.
     *
     * @param array $params
     *
     * @return void
     */
    public function set(array $params)
    {
        $this->params = [];
        foreach ($params as $group) {
            call_user_func_array([$this, 'add'], $group);
        }
    }

    /**
     * Add a parameter group.
     *
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return void
     */
    public function add(Parameters $params, FilterExpression $filters = null)
    {
        $this->params[] = [$params, $filters];
        $this->string   = null;
        $this->query    = null;
    }

    /**
     * toQueryString
     *
     * @param string $key
     *
     * @return string
     */
    public function toQueryString($key = 'jmg')
    {
        if (null === $this->query) {
            $this->query = array_map(function ($params) {
                return self::getQueryString($params[0], $params[1]);
            }, $this->params);
        }

        return http_build_query([$key => $this->query]);
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if (null !== $this->string) {
            return $this->string;
        }

        return $this->string = implode('|', array_map(function ($group) {
            list ($params, $filter) = $group;
            return (string)$params . ((null === $filter || 0 === count($filter->all())) ? '' :
                $params->getSeparator().$filter->getPrefix() . ':' . (string)$filter);
        }, $this->params));
    }

    /**
     * all
     *
     * @return array
     */
    public function all()
    {
        return $this->params;
    }

    /**
     * fromString
     *
     * @param string $params
     * @param string $separator
     * @param string $fpfx
     *
     * @return self
     */
    public static function fromString($params, $separator = '/', $fpfx = 'filter', $useSp = null)
    {
        $sp = $useSp ?: Parameters::P_SEPARATOR;

        return new self(array_map(function ($str) use ($separator, $fpfx, $sp) {
            list ($params, $filter) = array_pad(explode($fpfx.':', $str), 2, null);
            return [
                Parameters::fromString($params, $separator, $sp),
                $filter ? new FilterExpression($filter, $fpfx) : null
            ];
        }, explode('|', $params)));
    }

    /**
     * fromQuery
     *
     * @param array $query
     * @param string $key
     * @param string $separator
     * @param string $fpfx
     *
     * @return self
     */
    public static function fromQuery(array $query, $key = 'jmg', $separator = ':', $fpfx = 'filter')
    {
        if (!isset($query[$key])) {
            return new self;
        }

        if (is_string($query[$key])) {
            $string = $query[$key];
        } elseif (is_array($query[$key])) {
            $string = implode('|', $query[$key]);
        } else {
            throw new InvalidArgumentException('Argument mus be string or array.');
        }

        return self::fromString($string, $separator, $fpfx);
    }

    /**
     * @param Parameters $params
     * @param FilterExpression $filters
     * @param string $sp
     *
     * @return string
     */
    public static function getQueryString(Parameters $params, FilterExpression $filters = null, $sp = ':')
    {
        $fPrefix = null !== $filters && '' !== $filters->getPrefix() ? ':'. $filters->getPrefix() : '';
        $pStr    = str_replace($params->getSeparator(), $sp, (string)$params);

        return null === $filters || 0 === count($filters->all()) ?
            $pStr : sprintf('%s%s:%s', $pStr, $fPrefix, (string)($filters));
    }
}
