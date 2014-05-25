Frod
====================
[![Build Status](https://img.shields.io/travis/IonutBajescu/frod.svg)](https://travis-ci.org/IonutBajescu/frod)
[![Total Downloads](https://img.shields.io/packagist/dt/ionut/frod.svg)](https://packagist.org/packages/ionut/frod)
[![Latest Version](http://img.shields.io/packagist/v/ionut/frod.svg)](https://packagist.org/packages/ionut/frod)
[![Dependency Status](https://www.versioneye.com/php/ionut:frod/badge.svg)](https://www.versioneye.com/php/ionut:frod)

Frod is a Real Time Frontend Package Manager. <br/>
You can use packages(ex: jquery, bootstrap, semantic-ui) without need to manually download them.

Install with composer take only a few seconds.
```bash
composer require ionut/frod
```

Documentation(with Quick start) available on [frod.ionut-bajescu.com](http://frod.ionut-bajescu.com)

List with available packages on Frod Main Server you can find on  [server.frod.ionut-bajescu.com/packages](http://server.frod.ionut-bajescu.com/packages).

Examples
---

Example of basic use:
```php
<?php
include 'vendor/autoload.php';
?>
<html>
    <head>
        <?=Frod::packages('jquery', 'bootstrap')?>
    </head>
    <body>
        Frod is awesome!
    </body>
</html>
```

But wait, you have magical method `Frod::combine($package1, $package2)` to combine all packages in just two files(css and js). <br>
And you have `Frod::movable($package1, $package2)` to separate css and javascript(for put js bottom). <br>
And surprising, you can chain all methods.

Let me show you an example:
```php
<?php
include 'vendor/autoload.php';

$packages = Frod::combineMovable('jquery', 'bootstrap');
?>
<html>
    <head>
        <?=$packages->css?>
    </head>
    <body>
        Frod is <b>pure</b> awesome!

        <?=$packages->js?>
    </body>
</html>
```

License
---------------------

The Frod library is open-sourced software licensed under the MIT license.