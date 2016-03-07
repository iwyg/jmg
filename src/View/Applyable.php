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

/**
 * @interface Applyable
 *
 * @package Thapp\Jmg\View
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
interface Applyable
{
    /**
     * apply
     *
     * @param Task $task
     *
     * @return string
     */
    public function apply(Task $task);
}
