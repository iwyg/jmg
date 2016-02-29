# Jmg

Just In Time Image manipulation: Library for HTTP based image manipulation.

[![Author](http://img.shields.io/badge/author-iwyg-blue.svg?style=flat-square)](https://github.com/iwyg)
[![Source Code](http://img.shields.io/badge/source-lucid/signal-blue.svg?style=flat-square)](https://github.com/iwyg/jmg/tree/develop)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/iwyg/jmg/blob/develop/LICENSE.md)

[![Build Status](https://img.shields.io/travis/iwyg/jmg/develop.svg?style=flat-square)](https://travis-ci.org/iwyg/jmg)
[![Code Coverage](https://img.shields.io/coveralls/iwyg/jmg/develop.svg?style=flat-square)](https://coveralls.io/r/iwyg/jmg)
[![HHVM](https://img.shields.io/hhvm/thapp/jmg/dev-develop.svg?style=flat-square)](http://hhvm.h4cc.de/package/thapp/jmg)

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

Using the `ImageResolver` class, it is easy to resolve images from parameter strings.

```php
<?php

use Thapp\Jmg\Image\Processor;
use Thapp\Jmg\Resolver\PathResolver;
use Thapp\Jmg\Resolver\LoaderReslover;
use Thapp\Jmg\Resolver\ImageResolver;

$processor = new Thapp\Jmg\Image\Processor(
	new Thapp\Image\Driver\Gd\Source
);

$images = new ImageResolver($source, $pathResolver, $loaderResolver);

if ($resource = $res->resolve('images/source.jpg', Parameters::fromString('2/400/400/5'))) {
    header('Content-Type: image/jpeg');
    echo $resource->getContents();
}

```

## Core concepts

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
