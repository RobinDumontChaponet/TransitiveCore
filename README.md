# Transitive\Core

Very simple MVP (Model - View - Presenter) PHP framework.
Code base for many of my simples (or not) projects now.

~~I'm not doing yet another php framework. It would be pointless. More explanations ...later ;-)~~

[![Latest Stable Version](https://poser.pugx.org/transitive/core/v/stable?format=flat-square)](https://packagist.org/packages/transitive/core)
[![License](https://poser.pugx.org/transitive/core/license?format=flat-square)](https://packagist.org/packages/transitive/core)

## Installation

```sh
composer require transitive/core
```

## Basic Usage

```php
<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../config/default.php';

$transit = new Transitive\Core\WebFront();

$transit->addRouter(new Transitive\Core\PathRouter(PRESENTERS, VIEWS));

$request = @$_GET['request'];

$transit->execute($request ?? 'index');

$transit->print();

//echo $transit->getObContent();
```

## License

The MIT License (MIT)

Copyright (c) 2016 Robin Dumont-Chaponet

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
