<?php

/**
 * This File is part of the Thapp\Jmg package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Resolver;

use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;
use Thapp\Jmg\Cache\CacheInterface;
use Thapp\Jmg\ProcessorInterface;
use Thapp\Jmg\Resource\ImageResource;
use Thapp\Jmg\Loader\LoaderInterface;

/**
 * @trait ImageResolverHelper
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
trait ImageResolverHelper
{

    /**
     * extractParams
     *
     * @param string $params
     *
     * @return array
     */
    private function extractParamString($params)
    {
        list ($mode, $width, $height, $gravity, $background) = array_map(function ($value, $key = null) {
            return is_numeric($value) ? (int)$value : $value;
        }, array_pad(explode('/', $params), 5, null));

        if (0 > $mode || 3 < $mode || 0 === $mode) {
            $gravity = null;
        }

        if ($mode !== 3) {
            $background = null;
        }

        if (4 < $mode || 0 === $mode) {
            $height     = null;
            $gravity    = null;
        }

        if (0 == $mode) {
            $width = null;
        }

        $width  = ($mode !== 1 && $mode !== 2) ? $width : (int)$width;
        $height = ($mode !== 1 && $mode !== 2) ? $height : (int)$height;

        return compact('mode', 'width', 'height', 'gravity', 'background');
    }

    /**
     * extractFilters
     *
     * @param mixed $filters
     *
     * @return mixed
     */
    private function extractFilterString($filterStr = null)
    {
        if (null === $filterStr) {
            return;
        }

        if (0 === strpos($filterStr, 'filter:')) {
            $filterStr = substr($filterStr, 7);
        }

        return new FilterExpression;
    }

}
