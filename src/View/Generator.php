<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\View;

use Thapp\Jmg\Parameters;
use Thapp\Jmg\FilterExpression;
use Thapp\Jmg\ProcessorInterface;
use Thapp\Jmg\Resource\CachedResource;
use Thapp\Image\Geometry\GravityInterface;

/**
 * @class Generator
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Generator
{
    private $jmg;
    private $path;
    private $source;
    private $filters;
    private $parameters;
    private $task;

    public function __construct(Applyable $jmg, Task $task = null)
    {
        $this->jmg = $jmg;
        $this->filters = new FilterExpression([]);
        $this->parameters = new Parameters;
        $this->task = $task;
    }

    public function __clone()
    {
        $this->filters = clone $this->filters;
        $this->parameters = clone $this->parameters;
    }

    public function getTask()
    {
        return $this->task;
    }

    public function setTask(Task $task)
    {
        $this->task = $task;
    }

    /**
     * filter
     *
     * @param mixed $expr
     *
     * @return Generator.
     */
    public function filter($expr)
    {
        $this->filters->setExpression($expr);

        return $this;
    }

    /**
     * pixel
     *
     * @param mixed $px
     *
     * @return void
     */
    public function pixel($px)
    {
        $this->parameters->setMode(ProcessorInterface::IM_RSIZEPXCOUNT);
        $this->parameters->setTargetSize($px);

        return $this->apply();
    }

    /**
     * scale
     *
     * @param mixed $perc
     *
     * @return string
     */
    public function scale($perc)
    {
        $this->parameters->setMode(ProcessorInterface::IM_RSIZEPERCENT);
        $this->parameters->setTargetSize($perc);

        return $this->apply();
    }

    /**
     * fit
     *
     * @param mixed $width
     * @param mixed $height
     *
     * @return string
     */
    public function fit($width, $height)
    {
        $this->parameters->setMode(ProcessorInterface::IM_RSIZEFIT);
        $this->parameters->setTargetSize($width, $height);

        return $this->apply();
    }

    /**
     * cropAndResize
     *
     * @param mixed $width
     * @param mixed $height
     * @param int $gravity
     *
     * @return string
     */
    public function cropAndResize($width, $height, $gravity = GravityInterface::GRAVITY_CENTER)
    {
        $this->parameters->setMode(ProcessorInterface::IM_SCALECROP);
        $this->parameters->setTargetSize($width, $height);
        $this->parameters->setGravity($gravity);

        return $this->apply();
    }

    /**
     * crop
     *
     * @param mixed $width
     * @param mixed $height
     * @param int $gravity
     * @param mixed $background
     *
     * @return string
     */
    public function crop($width, $height, $gravity = GravityInterface::GRAVITY_CENTER, $background = null)
    {
        $this->parameters->setMode(ProcessorInterface::IM_CROP);
        $this->parameters->setTargetSize($width, $height);
        $this->parameters->setGravity($gravity);
        $this->parameters->setBackground($background);

        return $this->apply();
    }

    /**
     * get
     *
     * @return string
     */
    public function get()
    {
        $this->parameters->setMode(ProcessorInterface::IM_NOSCALE);

        return $this->apply();
    }

    /**
     * resize
     *
     * @param mixed $width
     * @param mixed $height
     *
     * @return string
     */
    public function resize($width, $height)
    {
        $this->parameters->setMode(ProcessorInterface::IM_RESIZE);
        $this->parameters->setTargetSize($width, $height);

        return $this->apply();
    }

    /**
     * end
     *
     * @return void
     */
    public function end()
    {
        if (null === $this->task || !$this->task->isChained()) {
            throw new \LogicException;
        }

        $params = $this->task->getParams();
        $newTask = $this->task->withArguments(
            false,
            $this->task->getPrefix(),
            $this->task->getSource(),
            $this->task->isTag(),
            $this->task->getAttributes(),
            $this->task->isQuery()
        );

        $newTask->setParams($params);

        $this->task = $newTask;

        return $this->apply(true);
    }

    /**
     * apply
     *
     * @return string
     */
    private function apply($finish = false)
    {
        if (!$finish) {
            $this->task->add($this->parameters, $this->filters);
        }

        return $this->jmg->apply($this->task);
    }
}
