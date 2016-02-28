<?php

/*
 * This File is part of the Thapp\Jmg\Image\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Image\Filter;

use Thapp\Image\Color\ColorInterface;
use Thapp\Jmg\ProcessorInterface;
use Thapp\Jmg\Filter\ColorizeFilterTrait as ColorizeHelper;

/**
 * @trait ColorizeFilterTrait
 *
 * @package Thapp\Jmg\Image\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
trait ColorizeFilterTrait
{
    use ColorizeHelper;

    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $this->setOptions($options);
        $image = $proc->getDriver();
        $color = $image->getPalette()->getColor((string)$this->getOption('c', hexdec('ffffff')));
        $image->filter($this->newFilter($color));
    }

    abstract protected function newFilter(ColorInterface $color);
}
