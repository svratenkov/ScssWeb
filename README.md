# ScssWeb - Scss Compiler & Watcher wtitten in PHP

[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/laravel/framework)

## About ScssWeb

ScssWeb is Scss Compiler & Watcher wtitten in PHP.

ScssWeb is:
	* fast due to effective file caching.
	* light, yet powerful. It can do all things that applications like Scout, Koala, ... can.
	* fully configurable.
	* cross-platformed as it runs on your local web server.
	* written as simple and ascetic as possible to give you real possibility to modify it as you need.

## Installation

ScssWeb uses well-known [leafo/scssphp]https://github.com/leafo/scssphp) compiler.
So you need composer installed on your computer to install ScssWeb. If you already have composer installed, skip step 1.


To install Composer please follow instructions on [Composer Getting Started page](https://getcomposer.org/doc/00-intro.md). 

**ScssWeb** source can be found at GitHub: <https://github.com/svratenkov/scssweb>.
Download **ScssWeb** source to any new directory in your localhost Document root.

Technically speeking, **ScssWeb** is a GUI for the third-party SCSS compiler [leafo/scssphp](https://github.com/leafo/scssphp) package.
To resolve this dependency you need Composer installed on your computer.
If you already have Composer installed, skip step 1.

### Step 1: Install Composer

To install Composer please follow instructions on [Composer Getting Started page](https://getcomposer.org/doc/00-intro.md).

### Step 2: Install ScssWeb

Having composer installed run your shell console, go to ScssWeb directory and run this command:
```php composer.phar install```

Composer will download the latest version of [leafo/scssphp](https://github.com/leafo/scssphp) package
and create class autoloader for **ScssWeb** application.

### Step 3: Run ScssWeb

You are ready to test **ScssWeb** application!
**ScssWeb** supports both classic URL style with `index.php?query`or
SEO-frendly style with query segments only.

Go to your browser and enter URL to **ScssWeb**, for example:

with `index.php?query`:
```http://localhost/path/to/scssweb/index.php```
with `.htaccess` redirecting:
```http://localhost/path/to/scssweb/```

You will see **ScssWeb** home page.

If you have any problems please ask any questions in the [ScssWeb issues page](https://github.com/svratenkov/scssweb/issues).

## Documentation

Description of the ScssWeb can be found on the home page.

## License

ScssWeb is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
