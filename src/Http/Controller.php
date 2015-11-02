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

use Thapp\Jmg\Http\ImageResponse;
use Thapp\Jmg\Resource\ResourceInterface;
use Symfony\Component\HttpFoundation\Request;
use Thapp\Jmg\Resolver\ImageResolverInterface;
use Thapp\Jmg\Resolver\RecipeResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @class Controller
 *
 * @package Thapp\Jmg\Http
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class Controller
{
    use ImageControllerTrait;

    /**
     * getImage
     *
     * @param Request $request
     * @param string $path
     * @param string $params
     * @param string $source
     * @param string $filter
     *
     * @return Response
     */
    public function getImageAction(Request $request, $path, $params, $source, $filter = null)
    {
        $this->setRequest($request);

        $this->getImage($path, $params, $source, $filter);
    }

    /**
     * getResource
     *
     * @param Request $request
     * @param string $recipe
     * @param string $source
     *
     * @return Response
     */
    public function getResource(Request $request, $recipe, $source)
    {
        $this->setRequest($request);

        $this->getResource($recipe, $source);
    }

    /**
     * getCached
     *
     * @param Request $request
     * @param string $prefix
     * @param string $id
     *
     * @return Response
     */
    public function getCachedAction(Request $request, $path, $id)
    {
        $this->setRequest($request);

        $this->getCached($path, $id);
    }
}
