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

use Thapp\Jmg\ParamGroup;

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
     * Signs a path.
     *
     * @param string $path
     * @param Parameters $params
     * @param FilterExpression $filters
     *
     * @return string
     */
    public function sign($path, ParamGroup $params);

    /**
     * Validates a given path against a paramgroup
     *
     * @param string $path
     * @param ParamGroup $params
     *
     * @return void
     */
    public function validate($path, ParamGroup $params);
}
