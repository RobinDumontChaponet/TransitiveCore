# Transitive\Core

$$ {\displaystyle \forall a,b,c\in X:(aRb\wedge bRc)\Rightarrow aRc} $$

---

Core primitives for the Transitive MVP stack.

This package provides the base presenter and view contracts used by the other Transitive packages, plus a lightweight `Transitive\Simple` implementation that can run on its own when you do not need the web-specific layer.

[![Latest Stable Version](https://poser.pugx.org/transitive/core/v/stable?format=flat-square)](https://packagist.org/packages/transitive/core)
[![License](https://poser.pugx.org/transitive/core/license?format=flat-square)](https://packagist.org/packages/transitive/core)

## What is included

- `Transitive\Core\Presenter`: stores presenter data and supports flow breaks with `redirect()`.
- `Transitive\Core\View`: the interface every Transitive view must implement.
- `Transitive\Core\ViewResource`: wraps a value and exposes helpers like `asArray()`, `asJSON()`, `asXML()`, `asYAML()`, `asString()`, and `asSerialized()`.
- `Transitive\Core\BreakFlowException`: used internally to interrupt route execution and redirect to another request.
- `Transitive\Simple\View`: a default in-memory view implementation for titles, typed content, and document serialization.
- `Transitive\Simple\Front`: a minimal front controller that executes routes and can export rendered output.

## Installation
```sh
composer require transitive/core
```

PHP `8.1+` is required.

## Basic usage
```php
<?php

use Transitive\Core\Presenter;
use Transitive\Simple\View;

$presenter = new Presenter();
$presenter->addData('name', 'Transitive');

$view = new View();
$view->setTitle('Home');
$view->addContent(function (array $data) {
	return 'Hello '.$data['name'];
});
$data = $presenter->getData();
$view->setData($data);

echo $view->getTitle('', '', PHP_EOL);
echo $view->getContent()->asString();
```

## View resources
`ViewResource` is the serialization boundary between views and consumers. A view can return structured content, and the caller can decide how to format it:

```php
$resource = $view->getDocument();

echo $resource->asJSON();
echo $resource->asXML('document');
```

## License

[MIT](LICENSE)
