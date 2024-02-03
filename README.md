<div align="center">

# Simple PHP View Library
![Status](https://img.shields.io/badge/status-development-blue)
![License](https://img.shields.io/badge/license-MIT-green.svg)

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
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/naingaunglwin-dev/view"
    }
  ],
  "require": {
    "naingaunglwin-dev/view": "dev-master"
  }
}
```
- Run the following command in your terminal from the project's root directory:
```bash
composer install
```

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
```
