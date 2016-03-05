<?php

/**
 * This File is part of the Thapp\Jmg package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg;

use Thapp\Image\Color\Parser;

/**
 * @class Parameters
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Parameters
{
    /** @var string **/
    const P_SEPARATOR = '/';

    /** @var string **/
    private $str;

    /** @var array **/
    private $params;

    /** @var string **/
    private $separator;

    /** @var array **/
    private static $qMap = [
        'scale'      => ProcessorInterface::IM_RSIZEPERCENT,
        'pixel'      => ProcessorInterface::IM_RSIZEPXCOUNT,
        'scale-crop' => ProcessorInterface::IM_SCALECROP,
        'crop'       => ProcessorInterface::IM_CROP,
        'resize'     => ProcessorInterface::IM_RESIZE,
        'resize-fit' => ProcessorInterface::IM_RSIZEFIT
    ];

    /**
     * Constructor.
     *
     * @param array $params
     * @param string $separator
     */
    public function __construct(array $params = [], $separator = self::P_SEPARATOR)
    {
        $this->params = $params;
        $this->separator = $separator;
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->str = null;
        $this->params = [];
    }

    /**
     * getSeparator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * setHeight
     *
     * @param int $width
     * @param int $height
     *
     * @return void
     */
    public function setTargetSize($width = null, $height = null)
    {
        $this->str = null;
        $this->params['width']  = $width;
        $this->params['height'] = $height;
    }

    /**
     * setMode
     *
     * @param int $mode
     *
     * @return void
     */
    public function setMode($mode)
    {
        $this->str = null;
        $this->params['mode']  = (int)$mode;
    }

    /**
     * setGravity
     *
     * @param int $gravity
     *
     * @return void
     */
    public function setGravity($gravity = null)
    {
        $this->str = null;
        $this->params['gravity'] = $gravity;
    }

    /**
     * setBackground
     *
     * @param string $color
     *
     * @return void
     */
    public function setBackground($background = null)
    {
        $this->str = null;

        if (null !== $background && $this->isColor($background)) {
            $this->params['background'] = $background;
        }
    }

    /**
     * all
     *
     * @return array
     */
    public function all()
    {
        $params = array_merge(static::defaults(), $this->params);

        return static::sanitize(
            $params['mode'],
            $params['width'],
            $params['height'],
            $params['gravity'],
            $params['background']
        );
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
    }

    /**
     * toQueryString
     *
     * @return string
     */
    public function toQueryString()
    {
        return '?' . http_build_query($this->all());
    }


    /**
     * asString
     *
     * @return string
     */
    public function asString()
    {
        if (null === $this->str) {
            $this->str = implode($this->separator, array_filter(array_values($this->all()), function ($val) {
                return null !== $val;
            }));
        }

        return $this->str;
    }

    /**
     * parseString
     *
     * @param string $paramString
     * @param string $separator
     *
     * @return array
     */
    public static function parseString($paramString, $separator = self::P_SEPARATOR)
    {
        $parts = array_pad(explode($separator, $paramString), 5, null);

        if (isset($parts[4]) && (!is_numeric($parts[0]) && !static::isHex($parts[4]))) {
            $parts[4] = null;
        }

        list($mode, $width, $height, $gravity, $background) = array_map(function ($value, $key = null) {
            return is_numeric($value) ? (int)$value : ltrim($value, ' #');
        }, $parts);

        return static::sanitize($mode, $width, $height, $gravity, $background);
    }

    /**
     * toChainedQueryString
     *
     * @param array $params
     * @param string $filter
     *
     * @return string
     */
    public static function toChainedQueryString(array $params, $filter = 'filter')
    {
        $q = array_map(function ($p) use ($filter) {
            list ($param, $filters) = array_pad($p, 2, null);
            return self::getChainedQString($param, $filters, $filter);
        }, $params);

        return http_build_query(['jmg' => $q]);
    }

    /**
     * Creates new Parameters from string.
     *
     * @param string $paramString
     * @param string $separator
     *
     * @return self
     */
    public static function fromString($paramString, $separator = self::P_SEPARATOR)
    {
        return new static(static::parseString($paramString, $separator), $separator);
    }

    /**
     * Creates new Parameters from query params.
     *
     * @param array $query
     *
     * @return self
     */
    public static function fromQuery(array $query)
    {
        $query = static::mapMode($query);

        $params = array_merge($default = static::defaults(), $query);

        if (null === $params['mode']) {
            $params['mode'] = 0;
        }

        extract(array_intersect_key($params, $default));

        return new static(static::sanitize($mode, $width, $height, $gravity, $background));
    }

    /**
     * @param array $query
     * @param string $filter
     *
     * @return array
     */
    public static function fromQueryChain(array $query, $filter = 'filter')
    {
        if (!isset($query['jmg'])) {
            return [];
        }

        return array_map(function ($qstr) use ($filter) {
            list($params, $filters) = array_pad(explode($filter.':', $qstr), 2, '');
            return [self::fromString($params, ':'), new FilterExpression($filters, $filter)];
        }, (array)$query['jmg']);

    }

    /**
     * isColor
     *
     * @param mixed $color
     *
     * @return bool
     */
    private function isColor($color)
    {
        return static::isHex($color);
    }

    /**
     * @param array $query
     *
     * @return array
     */
    private static function mapMode(array $query)
    {
        $map = [
            'mode'=> isset($query['mode']) ? (int)$query['mode'] : ProcessorInterface::IM_NOSCALE,
        ];

        if (empty($query)) {
            return $map;
        }

        $map = array_merge($map, static::$qMap);
        $type = array_reduce(array_keys($query), function ($a, $b) use ($map) {
            return isset($map[$b]) ? $b :
                (isset($map[$a]) ? $a :  'mode');
        });

        $query['mode'] = $map[$type];

        return $query;
    }

    /**
     * @param Parameters $params
     * @param FilterExpression $filters
     * @param string $ftl
     *
     * @return string
     */
    private static function getChainedQString(Parameters $params, FilterExpression $filters = null, $ftl = 'filter')
    {
        $pStr = str_replace($params->getSeparator(), ':', (string)$params);
        return null === $filters || 0 === count($filters->all()) ?
            $pStr : sprintf('%s:%s:%s', $pStr, $ftl, (string)($filters));
    }

    /**
     * @return array
     */
    private static function defaults()
    {
        return ['mode' => null, 'width' => null, 'height' => null, 'gravity' => null, 'background' => null];
    }

    /**
     * isHex
     *
     * @param mixed $color
     *
     * @return boolean
     */
    private static function isHex($color)
    {
        $color = ltrim($color, '#');

        return Parser::isHex($color);
    }

    /**
     * sanitize
     *
     * @param int $mode
     * @param int $width
     * @param int $height
     * @param int $gravity
     * @param string $background
     *
     * @return array
     */
    private static function sanitize($mode = null, $width = null, $height = null, $gravity = null, $background = null)
    {
        //$mode = max(0, min(6, (int)$mode));
        $mode = max(0, min(6, (int)$mode));
        $values = ['mode' => $mode];

        if (2 == $mode || 3 === $mode) {
            $values['gravity'] = $gravity ? (int)$gravity : 5;
        }

        if (3 === $mode && null !== $background) {
            $values['background'] = (is_int($background) && !Parser::isHex((string)$background)) ?
                $background : hexdec(Parser::normalizeHex($background));
        }

        switch ($mode) {
            case 1:
            case 2:
            case 3:
            case 4:
                $values['width'] = (int)$width;
                $values['height'] = (int)$height;
                break;
            case 5:
                $values['width'] = (float)$width;
                break;
            case 6:
                $values['width'] = (int)$width;
                break;
        }

        return array_merge(static::defaults(), $values);
    }
}
