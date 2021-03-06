<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg;

use RuntimeException;
use Thapp\Jmg\Exception\ProcessorException;

/**
 * @class AbstractProcessor
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
abstract class AbstractProcessor implements ProcessorInterface
{
    /** @var \Thapp\Jmg\FilterExpression */
    protected $filters;

    /** @var array */
    protected $options;

    /** @var \Thapp\Jmg\Resource\ResourceInterface */
    protected $resource;

    /** @var bool */
    protected $processed;

    /** @var string */
    protected $targetFormat;

    /** @var array */
    protected $targetSize;

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->processed    = false;
        $this->targetFormat = null;
        $this->targetSize   = null;

        $this->unload();
        $this->resource = null;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Parameters $parameters, FilterExpression $filters = null)
    {
        $this->loaded();

        $params = array_merge($this->defaultParams(), $parameters->all());

        $this->targetSize = [$params['width'], $params['height']];

        switch ($params['mode']) {
            case static::IM_NOSCALE:
                break;
            case static::IM_RESIZE:
                $this->resize();
                break;
            case static::IM_SCALECROP:
                $this->cropScale($params['gravity']);
                break;
            case static::IM_CROP:
                $this->crop($params['gravity'], $params['background']);
                break;
            case static::IM_RSIZEFIT:
                $this->resizeToFit();
                break;
            case static::IM_RSIZEPERCENT:
                $this->resizePercentual($params['width']);
                break;
            case static::IM_RSIZEPXCOUNT:
                $this->resizePixelCount($params['width']);
                break;
        }

        if (null === $filters) {
            return;
        }

        foreach ($filters->all() as $f => $parameter) {
            $this->addFilter($f, (array)$parameter);
        }
    }

    /**
     * set the output image format
     *
     * @param string $format
     *
     * @return void
     */
    public function setFileFormat($format)
    {
        $this->targetFormat = strtolower($format);
    }

    /**
     * get the image output format
     *
     * @return string
     */
    abstract public function getFileFormat();
    //{
        //if (null === $this->targetFormat) {
            //$this->targetFormat = $this->getSourceFormat();
        //}

        //return static::formatToExtension($this->targetFormat);
    //}

    /**
     * {@inheritdoc}
     */
    public function getFileExtension()
    {
        return static::formatToExtension($this->getFileFormat());
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFormat()
    {
        if (null === $this->resource || ($formats = array_flip(static::formats()))
            && !isset($formats[$mime = $this->resource->getMimeType()])) {
            return;
        }

        return $formats[$mime];
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceMimeType()
    {
        return $this->resource->getMimeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->translateFormatToMime($this->getFileFormat());
    }

    /**
     * get the image input source path
     *
     * @return string
     */
    public function getSource()
    {
        return $this->resource->getPath();
    }

    /**
     * Determine if the image has been processed yet.
     *
     * @return bool
     */
    public function isProcessed()
    {
        return (bool)$this->processed;
    }

    /**
     * getLastModTime
     *
     * @return integet
     */
    public function getLastModTime()
    {
        if ($this->processed) {
            return time();
        }

        return $this->resource->getLastModified();
    }


    /**
     * addFilter
     *
     * @param string $filter
     * @param array $options
     *
     * @return void
     */
    protected function addFilter($filter, array $options = [])
    {
        if (null === $this->filters) {
            return;
        }

        if (!$filters = $this->filters->resolve($filter)) {
            throw new RuntimeException('Filter "' . $filter . '" not found.');
        }

        $filters = array_filter($filters, function ($f) {
            return $f->supports($this);
        });

        if (empty($filters)) {
            throw new RuntimeException('No suitable filter found.');
        }

        foreach ($filters as $filter) {
            $filter->apply($this, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function loaded()
    {
        if (null === $this->getDriver()) {
            throw new ProcessorException('No image loaded.');
        }
    }


    /**
     * defaultParams
     *
     * @return array
     */
    private function defaultParams()
    {
        return [
            'mode'       => 0,
            'width'      => 100,
            'height'     => 100,
            'gravity'    => 0,
            'quality'    => 80,
            'background' => null,
        ];
    }

    /**
     * translateFormatToMime
     *
     * @param string $format
     *
     * @return string
     */
    protected function translateFormatToMime($format)
    {
        $formats = static::formats();

        if (!array_key_exists($format, $formats)) {
            return;
        }

        return $formats[$format];
    }

    /**
     * unload
     *
     * @return void
     */
    protected function unload()
    {
        if (null !== $this->resource && is_resource($handle = $this->resource->getHandle())) {
            fclose($handle);
        }

        $this->resource = null;
    }

    /**
     * formatToExtension
     *
     * @param string $format
     *
     * @return string
     */
    protected static function formatToExtension($format)
    {
        if ('jpeg' === $format) {
            return 'jpg';
        }

        return $format;
    }

    /**
     * formats
     *
     * @return array
     */
    protected static function formats()
    {
        return [
            'jpeg'  => 'image/jpeg',
            'jpg'   => 'image/jpeg',
            'png'   => 'image/png',
            'gif'   => 'image/gif',
            'tif'   => 'image/tiff',
            'tiff'  => 'image/tiff',
            'wbmp'  => 'image/vnd.wap.wbmp',
            'xbm'   => 'image/xbm',
        ];
    }
}
