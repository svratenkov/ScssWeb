<?php
/*
	ScssWeb - Scss compiler with file caching & web watcher
*/
use App\Controller;
use Micro\Request;

// Load autoloader
require 'vendor/autoload.php';

// Report all errors & warnings
error_reporting(-1);

// Http request parser detects requested page id
Request::parse();

// Call controller action
$response = Controller::call(Request::$query);

exit($response);

/**
 * View template helper: Generate an application URL.
 */
function url($uri, $abs = TRUE)
{
	return Request::url($uri, $abs);
}
