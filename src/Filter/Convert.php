<?php

/*
 * This File is part of the Thapp\Jmg\Filter package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Filter;

use InvalidArgumentException;
use Thapp\Jmg\ProcessorInterface;

/**
 * @class Format
 *
 * @package Thapp\Jmg\Filter
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Convert extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(ProcessorInterface $proc, array $options = [])
    {
        $this->setOptions($options);

        if (null == $format = $this->getOption('f')) {
            throw new InvalidArgumentException('Missing required option f.');
        }

        $proc->setFileFormat($format);
    }

    /**
     * {@inheritdoc}
     */
    protected function getShortOpts()
    {
        return ['f' => 'format'];
    }
}
