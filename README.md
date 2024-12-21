<div align="center">

# Simple PHP View Library

![Status](https://img.shields.io/badge/test-pass-green)
![Status](https://img.shields.io/badge/coverage-100%25-green)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

</div>

## Contributing
- This is an open-source library, and contributions are welcome.
- If you have any suggestions, bug reports, or feature requests, please open an issue or submit a pull request on the project repository.

## Requirement
- **PHP** version 8.0 or newer is required

## Installation & Setup
- You can just download the code from repo and use or download using composer.

### Download Using Composer
- If you don't have composer, install [composer](https://getcomposer.org/download/) first.
- create file `composer.json` at your project root directory.
- Add this to `composer.json`
```php
{
  "require": {
    "naingaunglwin-dev/view": "^1.0"
  }
}
```
- Run the following command in your terminal from the project's root directory:
```bash
composer install
```
- Or just run `composer require naingaunglwin-dev/view` in terminal.

## Usage
- In your php file,
```php
<?php

require_once "vendor/autoload.php";

use NAL\View\View;

$view = new View(__DIR__ . '/views');

// You can pass view file without extension if it is php file
$view->render('index', ['status' => 'success']);

// You can also render other file,
// You can retrieve the view without rendering,
$indexView = $view->render('index.html', [], true);

// You can also render multi views
$view->render(['index.html, test']);
```

- If you don't define your view path, View class will automatically set the view path to your project root level

### Section Usage
- create `one.php` and `two.php`
  
- one.php
```php
<?php //one.php

echo 'this is one.php';
$this->yield('content');
```

- two.php
```php
<?php //two.php
$this->extends('one'); // view file name that need to extend

$this->section('content'); // Section start with `content` name

echo '<br>This is section content from two.php';

$this->end('content'); // End the section `content`
```

- index.php
```php
<?php

require_once "vendor/autoload.php";

use NAL\View\View;

$view = new View(__DIR__ . '/views');

$view->render('two');
```

- Output will be
```txt
this is one.php
This is section content from two.php
```

### Custom Renderer Engine
```php
<?php
$view = new \NAL\View\View('path', MyCustomEngine::class);

$view->render('template'); // MyCustomEngine`s render method will be use in this process if exists

```
