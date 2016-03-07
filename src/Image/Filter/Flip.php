<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Image\Filter;

use Thapp\Image\Color\Parser;
use Thapp\Jmg\ProcessorInterface;
use Thapp\Image\Filter\Flip as FlipFilter;

/**
 * @class Rotate
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Flip extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $this->setOptions($options);

        $image = $proc->getDriver();
        $image->filter(new FlipFilter($this->getOption('m', FlipFilter::FLIP_BOTH)));
    }

    /**
     * {@inheritdoc}
     */
    protected function parseOption($option, $value)
    {
        return min(2, max(0, (int)$value));
    }

    /**
     * {@inheritdoc}
     */
    protected function getShortOpts()
    {
        return ['m' => 'mode'];
    }
}
