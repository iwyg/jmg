<?php

/*
 * This File is part of the Thapp\Jmg\Http package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Http;

use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;
use Symfony\Component\HttpFoundation\Request;

/**
 * @interface HttpSignerInterface
 *
 * @package Thapp\Jmg\Http
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface HttpSignerInterface
{
    /**
     * sing
     *
     * @param mixed $path
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return string
     */
    public function sign($path, Parameters $params, FilterExpression $filters = null);

    /**
     * validate
     *
     * @param Request $request
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return boolean
     */
    public function validate(Request $request, Parameters $params, FilterExpression $filters = null);
}
