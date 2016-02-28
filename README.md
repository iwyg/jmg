# Jmg

[![Author](http://img.shields.io/badge/author-iwyg-blue.svg?style=flat-square)](https://github.com/iwyg)
[![Source Code](http://img.shields.io/badge/source-lucid/signal-blue.svg?style=flat-square)](https://github.com/iwyg/jmg/tree/develop)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/iwyg/jmg/blob/develop/LICENSE.md)

[![Build Status](https://img.shields.io/travis/iwyg/jmg/develop.svg?style=flat-square)](https://travis-ci.org/iwyg/jmg)
[![Code Coverage](https://img.shields.io/coveralls/iwyg/jmg/develop.svg?style=flat-square)](https://coveralls.io/r/iwyg/jmg)
[![HHVM](https://img.shields.io/hhvm/thapp/jmg/dev-develop.svg?style=flat-square)](http://hhvm.h4cc.de/package/thapp/jmg)

## Just In Time Image manipulation: Library for HTTP based image manipulation

## Installation

```bash
> composer composer require thapp/jmg
```

## Run tests

```bash
> composer install
> vendor/bin/phpunit -c phpunit.xml.dist
```

## Quick start

```php
<?php

use Thapp\Jmg\Resolver\ImageResolver;

$processor = new Thapp\Jmg\Image\Processor(
	new Thapp\Image\Driver\Gd\Source
);

$images = new Thapp\Jmg\Resolver\ImageResolver(
	$processor,
	$pathResolver,
	$loaderResolver
	// ...
);

```
## Core Concepts

### Source loaders and resolvers

Jmg supports loading images from a variety of sources. In the example below,
lets assume we have a local filesystem that hosts our images.

```php
<?php

use Thapp\Jmg\Loader\FilesystemLoader;
use Thapp\Jmg\Resolver\LoaderReslover;
use Thapp\Jmg\Resolver\PathResolver;

$loaderResolver = new LoaderResolver;
$pathResolver = new PathResolver;

$pathResolver->add('local', __DIR__.'public/images');
$loaderResolver->add('local', new FilesystemLoader);

// tries to resolve a given prefix path;
if (!$loader === $loaderResolver->resolve('local')) // returns the FilesystemLoader {
    //then error
}

if (null === $path = $pathResolver->resolve('local')) {
    //then error
}

$src = $loader->load($path . '/image.jpg');


```

### Custom loaders

You may create your own loaders, e.g. for loading images from a remote source like an Amazon s3 storage or an ftp server.

Your custom loader must implement the `Thapp\Jmg\Loader\LoaderInterface` or simply extend from `Thapp\Jmg\Loader\AbstractLoader`.

```php

<?php

namespace Acme\Loaders;

use Thapp\Jmg\Loader\AbstractLoader

class AWSLoader extends AbstractLoader
{
    public function load($file)
    {
        //…
    }

    public function supports($path)
    {
        //…
    }
}

```


### Resolving images with parameters

Using the `ImageResolver` class, it is easy to resolve images from parameter strings.

```php
<?php


use Thapp\Jmg\Image\Processor;
use Thapp\Jmg\Resolver\PathResolver;
use Thapp\Jmg\Resolver\LoaderReslover;
use Thapp\Image\Driver\Imagick\Source;


$res = new ImageResolver(new Processor(new Source), $pathResolver, $loaderResolver);
$params = Parameters::fromString('2/400/400/5');

if ($resource = $res->resolve('images/source.jpg', $params)) {
    header('Content-Type: image/jpeg');
    echo $resource->getContents();
}


```

## Framework integration

Jmg comes prebundled with support for Laravel 5.* and Silex.

### Laravel 5.*

In `config/app.php`, add:

```php
<?php

$providers => [
    // …
    'Thapp\Jmg\Framework\Laravel\JmgServiceProvider'
];

$aliases => [
    // …
    'Jmg'      => 'Thapp\Jmg\Framework\Laravel\Facade\Jmg'
]

```
Then run

```bash
$ php artisan vendor:publish
```

from the command line.

`config/jmg.php`

**processor**
The processor, default is `image`. `imagine` is experimental and likely to be removed from future releases.

**driver**:
The image driver. Available drivers are `imagick`, `im` (imagemagick binary), and `gd`.

**convert_path**
If `im` is set for the driver, specify the path to the convert binary here.

**identify_path**
If `im` is set for the driver, specify the path to the identify binary here.

**paths**
Source paths aliases, e.g.

```php
'images' => public_path().'/images', // will be available at `/images/<params>/image.jpg`
'remote' => 'http://images.example.com' // may be empty if you use absolute urls
```

**loaders**

```php
'loaders' => [
    'images' => 'file',
    'remote' => 'http',
]
```

**disable\_dynamic\_processing**
Disables image processing via dynamic urls.

**mode\_constraints**
Set mode constraints on scaling values. This will only affect dynamic processing via URL.

**recipes**
Predefined image formats, e.g.

```php
'thumbs' => [
    'images', '1/0/400,filter:palette;p=rgb:clrz;c=#0ff' // will be available at `/thumbs/image.jpg`
],
```
**default\_cache**
The default caching type. Shipped types are `file`

**default\_cache\_path**
Directory path for local caches.

### Silex
