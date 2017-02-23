<?php
/*
	ScssWeb - Scss web compiler & watcher
*/
use App\Controller;
use Micro\Request;

// Composer's autoloader
require 'vendor/autoload.php';

// Report all errors & warnings
error_reporting(-1);

// Http request parser detects requested page
Request::parse();

// Make AppController instance
$controller = new Controller();

// Call controller action and retrieve response
// This	simple application uses ONLY ONE query param to address any of it's page
// So Request::$query is both a reguested page & a controller's action
$response = $controller->call(Request::$query);

exit($response);

/**
 * View template helper: Generate an application URL.
 */
function url($uri, $abs = TRUE)
{
	return Request::url($uri, $abs);
}
