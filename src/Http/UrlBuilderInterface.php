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

/**
 * @interface UrlBuilderInterface
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface UrlBuilderInterface
{
    /**
     * getUri
     *
     * @param Parameters $params
     * @param FilterExpression $filters
     * @param string $prefix
     *
     * @return string
     */
    public function getUri($source, Parameters $params, FilterExpression $filters = null, $prefix = '');
}
