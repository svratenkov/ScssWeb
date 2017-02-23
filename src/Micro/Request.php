<?php
/*
	Http Request - request parser & maker

	Supports Http request addressing modes:
		SEO mode:	http://example.com/[base/path]/query/segments
		Query mode:	http://example.com/[base/path]/index.php?query/segments

	Query string for Query mode is compatibile with SEO mode.
	It's format is: `?value1/value2`, rather then: `?var1=value1&var2=value2`.

	Main methods:
	-	Parse Request URL
	-	Make URL
	-	Redirect to URL
*/
namespace Micro;

class Request
{
	public static $redirected;			// Is request was redirected by .htaccess?

	public static $host;				// Server host: Http scheme (protocol) and server
	public static $base;				// Base path from DocRoot
	public static $segments;			// Query segments array
	public static $query;				// Query string

	/*
		Parse current request params
	*/
	public static function parse()
	{
		// Is request redirected by .htaccess?
		static::$redirected = isset($_SERVER['REDIRECT_URL']);

		// Server host
		static::$host = $_SERVER['HTTP_HOST'];

		// Request base and sements
		list(static::$base, static::$segments) = static::$redirected ? static::parse_redirect() : static::parse_request();

		// Empty segments mean home '/'
		if (! isset(static::$segments[0])) {
			static::$segments[0] = '/';
		}

		// Plain request query sring
		static::$query = implode('/', static::$segments);

		// Return request query segments
		return static::$segments;
	}

	/*
		Parse normal query request (without .htaccess redrecting):
			[http://localhost]/base/path/index.php?page_name

		Return: array[BaseUri, Query-Segments,... ]
	*/
	public static function parse_request()
	{
		$uri = trim($_SERVER['REQUEST_URI'], '/');

		$parts = explode('?', $uri);
		$base = array_shift($parts);

		$query = isset($parts[0]) ? explode('/', $parts[0]) : [];

		return [ $base, $query ];
	}

	/*
		Parse redirected request (with .htaccess redrecting):
			[http://localhost]/base/path/page_name

		Return: array[BaseUri, Query-Segments,... ]
	*/
	public static function parse_redirect()
	{
		// base_url - the first similar sements of REQUEST_URI & SCRIPT_NAME
		$url_parts = explode('/', trim($_SERVER['REDIRECT_URL'], '/'));
		$script_parts = explode('/', ltrim($_SERVER['SCRIPT_NAME'], '/'));
		$base_parts = $query_parts = [];

		// base_parts - first identical segments of URL and Script, и path_parts - all the rest
		foreach ($url_parts as $key => $segment) {
			if (empty($path_parts) and isset($script_parts[$key]) and $script_parts[$key] == $segment) {
				$base_parts[] = $segment;
			}
			else {
				$query_parts[] = $segment;
			}
		}

		return [ implode('/', $base_parts), $query_parts ];
	}

	/*
		This site Base URL - absolute or relative
	*/
	public static function base_url($abs = TRUE)
	{
		return ($abs ? 'http://'.static::$host : '').(static::$base ? '/'.static::$base : '');
	}

	/*
		This site URL for given URI (or for current reguest) - absolute or relative
	*/
	public static function url($uri, $abs = TRUE)
	{
		$uri = trim($uri, '/');
		$query = $uri ? (static::$redirected ? '/' : '?').$uri : '';

		return static::base_url($abs).$query;
	}

	/*
		Redirect to the given page
	*/
	public static function redirect($uri)
	{
		header('Location: '.static::url($uri));
		exit;
	}
}
