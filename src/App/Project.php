<?php
/*
	ScssWeb project - projects config
	-	Holds current project name and attributes
	-	Stores recently used projects
*/
namespace App;

class Project
{
	// Recently used projects config array, stored in the config/projects.php
	public static $projects;

	// Active project object
	public static $active;

	// Projects config file name
	public static $config_file = 'projects.php';

	// Project name
	public $name;

	// Project directories & files
	public $scss_file;
	public $css_file;
	public $cache_dir;
	
	// CSS file style: 0-'Compact',1-'Compressed',2-'Crunched',3-'Debug',4-'Expanded',5-'Nested'
	public $css_style;

	// CSS file signature
	public $signature;

	/**
	 *	Activate project given by name OR found in session
	 * 	Returns new active project
	 */
	public static function activate($name = NULL)
	{
		// Load recent projects from config if not loaded yet
		if (is_null(static::$projects) and file_exists(static::$config_file)) {
			static::$projects = include static::$config_file;
		}

		if (empty(static::$projects)) {
			return;
		}

		// Resolve given project name
		if (is_null($name = static::resolve_name($name))) {
			return;
		}

		// Set active project
		if (is_null(static::$active)) {
			static::$active = new static();
		}
		else if ($name == static::$active->name) {
			// already active
			return static::$active;
		}

		// Set new active project name, update session
		static::$active->name = $_SESSION['active'] = $name;

		// Set new active project params from config
		foreach (static::$projects[$name] as $key => $val) {
			static::$active->{$key} = $val;
		}

		return static::$active;
	}

	/**
	 *	Resolve given desired active project name
	 */
	public static function resolve_name($name)
	{
		// Check arg - it could be entered manually - simply change it to active project name found in config
		if (is_null($name)) {
			// Find active project in the session vars
			if (isset($_SESSION['active'])) {
				$name = $_SESSION['active'];
			}
			else {
				// If no active project found in session - set first project active
				reset(static::$projects);
				return key(static::$projects);
			}
		}

		if (array_key_exists($name, static::$projects)) {
			return $name;
		}
	}

	/**
	 * Clear active project cache
	 */
	public static function clearCache()
	{
		// Setup cache dir
		$cache_dir = static::$active->cache_dir;

		if (! ($dir = realpath($cache_dir)) OR ! is_dir($dir)) {
			throw new \Exception("Can't find cache directory `{$cache_dir}`");
		}

		$files = scandir($dir);
		$dir .= DIRECTORY_SEPARATOR;

		foreach ($files as $file) {
			if ($file != '.' && $file != '..' && filetype($dir.$file) != 'dir') {
				unlink($dir.$file);
			}
		}
	}
}