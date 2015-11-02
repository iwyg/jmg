<?php

/*
 * This File is part of the Thapp\Jmg package
 *
 * (c) iwyg <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Framework\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Routing\Registrar as Router;
use Thapp\Jmg\Framework\Common\ProviderHelperTrait;

/**
 * @class JmgServiceProvider
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author iwyg <mail@thomas-appel.com>
 */
class JmgServiceProvider extends ServiceProvider
{
    use ProviderHelperTrait;

    const VERSION = '1.0.0-dev';

    /**
     * defer
     *
     * @var boolean
     */
    protected $defer = false;

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->prepareConfig();

        $this->app->singleton(
            'Thapp\Jmg\Resolver\ImageResolverInterface',
            'Thapp\Jmg\Resolver\ImageResolver'
        );

        $proc = $this->app['config']->get('jmg.processor', 'image');


        $procClass = 'imagine' === $proc ?
            'Thapp\Jmg\Imagine\Processor' :
            'Thapp\Jmg\Image\Processor';

        $this->app->singleton('Thapp\Jmg\ProcessorInterface', $procClass);

        $this->app->when('Thapp\Jmg\Image\Processor')
            ->needs('Thapp\Image\Driver\SourceInterface')
            ->give($this->getSourceClass($this->app['config']->get('jmg.driver', 'imagick')));

        if ('imagine' === $proc) {
            $this->app->when('Thapp\Jmg\Imagine\Processor')
                ->needs('Imagine\Image\ImagineInterface')
                ->give($this->getImagineClass($this->app['config']->get('jmg.driver', 'imagick')));
        } elseif ('im' === $this->app['config']->get('jmg.driver')) {

            $this->app->singleton('Thapp\Image\Driver\Im\Identify', function () {
                return new \Thapp\Image\Driver\Im\Identify(
                    new \Thapp\Image\Driver\Im\Shell\Command,
                    $this->app['config']->get('jmg.identify_path', 'identify')
                );
            });
            $this->app->singleton('Thapp\Image\Driver\Im\Convert', function () {
                return new \Thapp\Image\Driver\Im\Convert(
                    new \Thapp\Image\Driver\Im\Shell\Command,
                    $this->app['config']->get('jmg.convert_path', 'convert'),
                    $this->app->make('Illuminate\Log\Writer')
                );
            });
        }



        $this->app->singleton(
            'Thapp\Jmg\Resolver\FilterResolverInterface',
            'Thapp\Jmg\Resolver\FilterResolver'
        );

        $this->app->singleton(
            $loader = 'Thapp\Jmg\Resolver\LoaderResolverInterface',
            'Thapp\Jmg\Framework\Laravel\Resolver\LazyLoaderResolver'
            //'Thapp\Jmg\Resolver\LoaderResolver'
        );

        $this->app->singleton(
            'Thapp\Jmg\Resolver\CacheResolverInterface',
            'Thapp\Jmg\Framework\Laravel\Resolver\LazyCacheResolver'
            //'Thapp\Jmg\Resolver\CacheResolver'
        );

        $this->app->singleton(
            'Thapp\Jmg\Http\UrlBuilderInterface',
            'Thapp\Jmg\Http\UrlBuilder'
        );

        if ($this->app['config']->get('jmg.secure', false)) {
            $this->app->singleton('Thapp\Jmg\Http\HttpSignerInterface', function ($app) {
                return new \Thapp\Jmg\Http\UrlSigner(
                    $app['config']->get('jmg.token_secret'),
                    $app['config']->get('jmg.token_key', 'token')
                );
            });
        }

        $this->app->singleton('Thapp\Jmg\Validator\ValidatorInterface', function ($app) {
            return new \Thapp\Jmg\Validator\ModeConstraints($app['config']['jmg']['mode_constraints']);
        });

        $this->app->singleton('Thapp\Jmg\Resolver\RecipeResolverInterface', function ($app) {
            return new \Thapp\Jmg\Resolver\RecipeResolver($app['config']['jmg']['recipes']);
        });

        $this->app->singleton('Thapp\Jmg\Resolver\PathResolverInterface', function ($app) {
            return new \Thapp\Jmg\Resolver\PathResolver($app['config']['jmg']['paths']);
        });

        $this->app->resolving($class = $this->getControllerClass(), function ($ctrl, $app) {
            $ctrl->setRecieps($app->make('Thapp\Jmg\Resolver\RecipeResolverInterface'));
            if ($app['config']->get('jmg.secure', false)) {
                $ctrl->setUrlSigner($app->make('Thapp\Jmg\Http\HttpSignerInterface'));
            }
        });

        // fire an event in case the processor gets instantiated
        $this->app->resolving('Thapp\Jmg\ProcessorInterface', function ($proc, $app) {
            $app['events']->fire('jmg.processor.boot');
        });

        // set options on imagine processor
        $this->app->resolving('Thapp\Jmg\Imagine\Processor', function ($proc, $app) {
            $proc->setOptions($app['config']->get('jmg.imagine'), []);
        });

        // set options on image processor
        $this->app->resolving('Thapp\Jmg\Image\Processor', function ($proc, $app) {
            $proc->setOptions($app['config']->get('jmg.image'), []);
        });

        $this->app->singleton('jmg', function ($app) {
            return new \Thapp\Jmg\View\Jmg(
                $app->make('Thapp\Jmg\Resolver\ImageResolverInterface'),
                $app->make('Thapp\Jmg\Resolver\RecipeResolverInterface'),
                $app->make('Thapp\Jmg\Http\UrlBuilderInterface'),
                $app['config']->get('jmg.cache_path_prefix', 'cached')
            );
        });

        $this->app->alias('Thapp\Jmg\Resolver\LoaderResolverInterface', 'jmg.loaders');
        $this->app->alias('Thapp\Jmg\Resolver\FilterResolverInterface', 'jmg.filters');

        $this->registerCommands();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->registerRoutes();

        $this->publishes([
            __DIR__.'/resource/config.php' => config_path('jmg.php'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['jmg', 'jmg.loaders', 'jmg.caches', 'jmg.filters'];
    }

    /**
     * registerRoutes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (file_exists(storage_path().'/framework/routes.php')) {
            return;
        }

        $router = $this->app['router'];

        if (!$this->app['config']['jmg']['disable_dynamic_processing']) {
            $this->registerDynamicRoutes($router);
        }

        $this->registerRecipes($router);
        $this->registerCached($router);
    }

    /**
     * registerCached
     *
     * @param mixed $router
     *
     * @return void
     */
    private function registerCached($router)
    {
        $ctrl   = $this->getControllerClass().'@getCachedResponse';
        $caches = $this->app['config']['jmg.caches'];
        $prefix = $this->app['config']['jmg.cache_path_prefix'];

        foreach ($this->app['config']->get('jmg.paths', []) as $alias => $path) {
            if (isset($caches[$alias]) && false === $caches[$alias]) {
                continue;
            }

            $this->registerCachedController($router, $ctrl, $alias, $prefix);
        }
    }

    /**
     * registerRecipes
     *
     * @param mixed $router
     *
     * @return void
     */
    protected function registerRecipes($router)
    {
        $config = $this->app['config']['jmg'];
        $ctrl = $this->getControllerClass().'@getResourceResponse';

        foreach ($config['recipes'] as $recipe => $data) {
            if (isset($config['paths'][$data[0]])) {
                $this->registerRecipesController($router, $ctrl, $recipe);
            }
        }
    }

    protected function registerDynamicRoutes($router)
    {
        list (, $params, $source, $filter) = $this->getPathRegexp();

        $pattern = '/{params}/{source}/{filter?}';
        $controller = $this->getControllerClass().'@getImageResponse';

        foreach ($this->app['config']['jmg']['paths'] as $path => $filePath) {
            $this->registerDynamicController($router, $controller, $path, $pattern, $params, $source, $filter);
        }
    }

    /**
     * registerCaches
     *
     * @return void
     */
    protected function registerCaches()
    {
        $config = $this->app['config']['jmg'];

        foreach ($config['paths'] as $prefix => $path) {
        }
    }

    protected function getControllerClass()
    {
        return '\Thapp\Jmg\Framework\Laravel\Http\Controller';
    }

    /**
     * prepareConfig
     *
     * @return void
     */
    protected function prepareConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/resource/config.php', 'jmg');
    }

    /**
     * registerCommands
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->app->singleton('command.jmg.clearcache', function ($app) {
            return new \Thapp\Jmg\Framework\Laravel\Console\ClearCacheCommand(
                $app->make('Thapp\Jmg\Cache\CacheClearer')
            );
        });

        $this->commands('command.jmg.clearcache');
    }

    /**
     * registerDynamicController
     *
     * @param Router $router
     * @param mixed $path
     * @param string $pattern
     * @param string $params
     * @param string $source
     * @param string $filter
     *
     * @return void
     */
    private function registerDynamicController(Router $router, $ctrl, $path, $pattern, $params, $source, $filter)
    {
        $router->get('/'.($path = trim($path, '/')) . $pattern, $ctrl)
            ->defaults('request', null)
            ->defaults('path', $path)
            ->where('path', $path)
            ->where('params', $params)
            ->where('source', $source)
            ->where('filter', $filter);
    }

    /**
     * registerRecipesController
     *
     * @param mixed $router
     * @param mixed $ctrl
     * @param mixed $recipe
     *
     * @return void
     */
    private function registerRecipesController(Router $router, $ctrl, $recipe)
    {
        $router->get(rtrim($recipe, '/').'/{source}', $ctrl)
            ->where('source', '(.*)');
    }

    /**
     * registerCachedController
     *
     * @param Router $router
     * @param mixed $path
     * @param mixed $suffix
     *
     * @return void
     */
    private function registerCachedController(Router $router, $ctrl, $path, $suffix)
    {
        $router->get('/'.trim($suffix, '/').'/{path}/{id}', $ctrl)
            ->where('path', '(.*)')
            ->where('id', '(.*\/){1}.*')
            ->where('suffix', $suffix)
            ->defaults('path', $path);
    }
}
