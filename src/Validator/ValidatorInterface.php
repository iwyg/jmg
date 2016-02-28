<?php

/**
 * This File is part of the Thapp\Jmg package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Validator;

/**
 * @interface ValidatorInterface ValidatorInterface
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
interface ValidatorInterface
{
    /**
     * Validates a value against given values
     *
     * @param mixed $value
     * @param array $values
     *
     * @return boolean
     */
    public function validate($value, array $values = []);
}
