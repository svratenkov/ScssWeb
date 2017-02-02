<?php
/*
	Http Request - request parser & maker
	This simple app has only one level of pages, so any query has only one segment

	Http request addressing modes:
		Script-mode:	http://example.com/<base>/<path>/script.php?page_name
		SEO-mode:		http://example.com/<base>/<path>?page_name

	Main methods:
	-	Parse Request URL
	-	Make URL
	-	Redirect to URL
*/
namespace Micro;

class Request
{
	// Http scheme (protocol) and server
	public static $host;				// Server host

	// Http URL
	public static $base;				// Base path of this site in DocRoot
	public static $query;				// Query URI - path from base to requested page (root path ==> '/')
//	public static $segments;			// Path segments array

	// Is request was redirected by .htaccess?
	public static $redirected;

	/*
		Parse current request params
	*/
	public static function parse()
	{
		// Is request redirected by .htaccess?
		static::$redirected = isset($_SERVER['REDIRECT_URL']);

		// Server host
		static::$host = $_SERVER['HTTP_HOST'];

		// Domain & Script name
		list(static::$base, static::$query) = static::$redirected ? static::parse_redirect() : static::parse_request();

		// Return request query segments
		return static::$query;
	}

	/*
		Parse normal query request (without .htaccess redrecting):
			[http://localhost]/base/path/index.php?page_name

		Return: array[BaseUri, Query]
	*/
	public static function parse_request()
	{
		$uri = trim($_SERVER['REQUEST_URI'], '/');

		$parts = explode('?', $uri);

		$base = $parts[0];								// --> "base/path/index.php"
		$query = isset($parts[1]) ? $parts[1] : '/';

		return [$base, $query];
	}

	/*
		Parse redirected request (with .htaccess redrecting):
			[http://localhost]/base/path/page_name

		Return: array[BaseUri, Query]
	*/
	public static function parse_redirect()
	{
		// base_url - the first similar sements of REQUEST_URI & SCRIPT_NAME
		$url_parts = explode('/', trim($_SERVER['REDIRECT_URL'], '/'));
		$script_parts = explode('/', ltrim($_SERVER['SCRIPT_NAME'], '/'));
		$base_parts = $query_parts = [];

		// Ищем base - первые совпадающие сегменты URL и скрипта, и path - оставшиеся
		foreach ($url_parts as $key => $segment) {
			if (empty($path_parts) and isset($script_parts[$key]) and $script_parts[$key] == $segment) {
				$base_parts[] = $segment;
			}
			else {
				$query_parts[] = $segment;
			}
		}

		$base = implode('/', $base_parts);
		$query = implode('/', $query_parts) ?: '/';

		return [$base, $query];
	}

	/*
		Base URL ЭТОГО сайта - абсолютный или относительный
	*/
	public static function base_url($abs = TRUE)
	{
		return ($abs ? 'http://'.static::$host : '').(static::$base ? '/'.static::$base : '');
	}

	/*
		URL ЭТОГО сайта для заданного URI - абсолютный или относительный
		Если URI не задан, берем URI запроса
	*/
	public static function url($uri, $abs = TRUE)
	{
		$uri = trim($uri, '/');
		$query = $uri ? (static::$redirected ? '/' : '?').$uri : '';

		return static::base_url($abs).$query;
	}

	/*
		Редирект на заданную страницу
	*/
	public static function redirect($uri)
	{
		header('Location: '.static::url($uri));
		exit;
	}
}
