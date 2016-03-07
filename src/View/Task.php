<?php

/*
 * This File is part of the Thapp\Jmg\View package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\View;

use Thapp\Jmg\ParamGroup;
use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;

/**
 * @class Task
 *
 * @package Thapp\Jmg\View
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Task
{
    /** @var bool */
    private $chained;

    /** @var string */
    private $prefix;

    /** @var string */
    private $src;

    /** @var bool */
    private $asTag;

    /** @var array */
    private $attrs;

    /** @var bool */
    private $asQuery;

    /** @var ParamGroup */
    private $params;

    /**
     * Constructor.
     *
     * @param bool $chained
     * @param string $prefix
     * @param string $src
     * @param bool $asTag
     * @param array $attrs
     * @param bool $asQuery
     */
    public function __construct($chained, $prefix, $src, $asTag = false, array $attrs = null, $asQuery = true)
    {
        $this->chained    = (bool)$chained;
        $this->prefix     = $prefix;
        $this->src        = $src;
        $this->asTag      = $asTag;
        $this->attributes = $attrs;
        $this->asQuery    = (bool)$asQuery;
    }

    /**
     * getPrefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * getSource
     *
     * @return string
     */
    public function getSource()
    {
        return $this->src;
    }

    /**
     * isChained
     *
     * @return bool
     */
    public function isChained()
    {
        return $this->chained;
    }

    /**
     * isQuery
     *
     * @return bool
     */
    public function isQuery()
    {
        return $this->asQuery;
    }

    /**
     * Taks is tag.
     *
     * @return bool
     */
    public function isTag()
    {
        return $this->asTag;
    }

    /**
     * Get attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes ?: [];
    }

    /**
     * Adds params and filters
     *
     * @param Parameters $params
     * @param FilterExpression $filter
     *
     * @return void
     */
    public function add(Parameters $params, FilterExpression $filter = null)
    {
        $this->getParams()->add($params, $filter);
    }

    /**
     * Get the parameters.
     *
     * @return ParamGroup
     */
    public function getParams()
    {
        if (null === $this->params) {
            $this->params = new ParamGroup;
        }

        return $this->params;
    }

    /**
     * Set the parameter.
     *
     * @param ParamGroup $params
     *
     * @return void
     */
    public function setParams(ParamGroup $params)
    {
        $this->params = $params;
    }

    /**
     * return a cloned version;
     *
     * @param bool $chained
     * @param string $prefix
     * @param string $src
     * @param bool $asTag
     * @param array $attrs
     * @param bool $asQuery
     *
     * @return self
     */
    public function withArguments($chained, $prefix, $src, $asTag = false, array $attrs = null, $asQuery = true)
    {
        $copy = clone ($this);
        $copy->chained    = (bool)$chained;
        $copy->prefix     = $prefix;
        $copy->src        = $src;
        $copy->asTag      = $asTag;
        $copy->attributes = $attrs;
        $copy->asQuery    = (bool)$asQuery;

        return $copy;
    }

    public function __clone()
    {
        $this->params = null;
    }
}
