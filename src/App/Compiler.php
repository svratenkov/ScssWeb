<?php
/**
 * SCSSPHP + FileCached @import Files Compiler
 */
namespace App;

use Leafo\ScssPhp\Compiler as LeafoCompiler;

/**
 * SCSS Cached compiler
 */
class Compiler extends LeafoCompiler
{
	// Avail output CSS file styles: 0-'Compact',1-'Compressed',2-'Crunched',3-'Debug',4-'Expanded',5-'Nested'
	static public $css_formats = [ 'Compact', 'Compressed', 'Crunched', 'Debug', 'Expanded', 'Nested', ];

	// Cache directory with trailing DIRECTORY_SEPARATOR
	public $cache_dir;

	// Imports register file name
	public $imports_fname;

	// Forced compilation flag to be known by importFile()
	public $forced;

	/**
	 * Constructor
	 *
	 * @param string|array			$config		Config array or filename
	 */
	public function __construct($cache_dir, $format = NULL)
	{
		// Setup cache dir
		if (! ($path = realpath($cache_dir)) OR ! is_dir($path)) {
			throw new \Exception("Can't find cache directory `{$cache_dir}`");
		}
		$this->cache_dir = $path.DIRECTORY_SEPARATOR;

		parent::__construct();

		$this->setCssFormat($format);
	}

    /**
     * Compile scss file with checking if input root and import cscc files are newer then output css
     *
     * @param string $in		-	path to input sss file
     * @param string $out		-	path to output scss file
     * @param bool   $forced	-	TRUE - forced compilation regardless of any time conspirations
     * 
     * @return string|null		-	compiled css code or NULL if no changes
     */
	public function compileChecked($in, $out, $forced = FALSE)
	{
		// Check source SCSS file existance
		if (! ($path = realpath($in)) OR ! is_readable($path)) {
			throw new \Exception("Source SCSS file `{$in}` doesn't exist or unreadable");
		}
		$in = $path;

		// Canonicalize output CSS file name
		$out = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $out);

		// Load imports from recent compile cache
		$imports = $this->importsLoad();

		// Forced? - given or imports register file unexists
		$forced = $forced || is_null($imports);

		// Return if nothing to compile
		if (! $forced AND ! $this->needsCompile($out, $in, $imports)) {
			return;
		}

		// Compile newer files only
		$css = $this->compileFile($in, $forced);

		// Save imports to cache for this compile
		if (! is_null($css)) {
			$this->importsSave($this->getImportedFiles());
		}

		return $css;
	}

	/**
	 * Determine whether .scss file needs to be re-compiled.
	 * If root scss need to be recompiled it appends to $this->comiled array.
	 *
	 * @param string $out		Output css file path
	 * @param string $in		Input scss file path
	 * @param array  $imports	Array of all previously parsed scss files, including root scss
	 *
	 * @return boolean			True if compile needed.
	 */
	public function needsCompile($out, $in, $imports = NULL)
	{
		// Check for output css existence
		if (! is_file($out)) {
			return TRUE;
		}

		$mtime = filemtime($out);

		// Check if input scss is older then output css
		if (filemtime($in) > $mtime) {
			return TRUE;
		}

		// Check if recently imported scss is older then output css
		foreach ((array) $imports as $file) {
			if (filemtime($file) > $mtime) {
				return TRUE;
			}
		}

		return FALSE;
	}

    /**
     * Compile scss file
     *
     * @param string $in	-	path to scss file
     *
     * @return string		-	compiled css code
     */
	public function compileFile($in, $forced = FALSE)
	{
		// Set forced flag to be known by importFile()
		$this->forced = $forced;
	
		// From scssphp docs:
		//	When you import a file using the @import directive, the current path of your PHP script
		//	is used as the search path by default. This is often not what you want, so there are
		//	two methods for manipulating the import path: addImportPath, and setImportPaths.
		$this->addImportPath(pathinfo($in, PATHINFO_DIRNAME));

		$css = $this->compile(file_get_contents($in), $in);

		return $css;
	}

    /**
     * Import file CSS TREE with File Caching - overloading the original method
     * 
     * Original parent method saves PARSED file tree in the MEMORY CACHE array
     * Any sabsequent import within the current compilation will use tree from this cache
     * This overload a//dds FILE CACHING to be used BETWEEN DIFFERENT COMPILATIONS
     * 
     * @param string $path
     * @param array  $out
     */
	protected function importFile($path, $out)
	{
		$realPath = realpath($path);

		// see if tree is cached
		if (isset($this->importCache[$realPath])) {
			// css tree cached in memory - call parent method
			parent::importFile($realPath, $out);
		}
		else if (! $this->forced AND ($tree = $this->cacheLoadTree($realPath))) {
			// css tree found in file cache - save it to cache memory and call parent method
			$this->importCache[$realPath] = $tree;
			parent::importFile($realPath, $out);
		}
		else {
			// css tree not found anywhere - call parent method and save tree from memory to file cache
			parent::importFile($realPath, $out);
			$this->cacheSaveTree($realPath, $this->importCache[$realPath]);
		}
    }

	/*
		Return protected $this->importedFiles
	*/
	public function getImportedFiles()
	{
	//	return $this->importedFiles;				// Not canonicalised
		return array_keys($this->importCache);		// canonicalised ('/\' => DIRECTORY_SEPARATOR)
	}

	/*
		Return $this->compiledFiles
	*/
	public function getCompiledFiles()
	{
		return $this->sourceNames;
	}

	/*
		Set output css file format (style)
		Avail CSS file styles: 0-'Compact',1-'Compressed',2-'Crunched',3-'Debug',4-'Expanded',5-'Nested'
	*/
	public function setCssFormat($key = 4)
	{
		$this->setFormatter('Leafo\\ScssPhp\\Formatter\\'.static::$css_formats[$key]);
	}

    /**
     * Save cached tree for given Import file
     *
     * @param string $path
     */
	public function cacheSaveTree($path, $tree)
	{
		$cache = $this->cacheName($path);

		if (! file_put_contents($cache, serialize($tree))) {
			throw new \Exception("Can't write cache `{$cache}` for `{$path}`");
		}
	}

	/**
	 * Load cached tree for given scss file, IF SCSS IS OLDER THEN CACHE
	 * 
	 * @param string	$path
	 * @param bool		$check	- check times of original and cached files
	 */
	public function cacheLoadTree($path)
	{
		$cache = $this->cacheName($path);

		// If scss file is newer then cache, it needs to recompile
		if (! is_file($cache) or filemtime($path) > filemtime($cache)) {
			return;
		}

		if (($content = file_get_contents($cache)) === FALSE) {
			throw new \Exception("Can't read cache `{$cache}` for `{$path}`");
		}

		return unserialize($content);
	}

	/**
	     * Get path to cached .scss file
	 * 		$fname		- abs path @name
	 * 		file name	- mnemonic name
	 * 		md5($fname)	- unique file @id
	 * 
	 * @return string
	 */
	public function cacheName($fname)
	{
		$pathinfo = pathinfo($fname);
		return $this->cache_dir.$pathinfo['filename'].'_'.md5($fname).'.'.$pathinfo['extension'];
    }

	/**
	 * Get file name of the imports register
	 *
	 * @param array  $imports
	 */
	public function importsName()
	{
		static $fname;

		return $fname ?: ($fname = $this->cache_dir.'imports_register.json');
	}
	/**
	 * Load imports from register file
	 * Register file content meanings:
	 * 		!exist	- register doesn't exist, imports unknown, needs forced compile
	 * 		[]		- recent register, imports known and unexist, main scss surelly didn't contain any imports recently
	 * 		[...]	- recent register, imports known and exist, register contain all recent imports
	 */
	public function importsLoad()
	{
		// Save file name for later use in importsSave()
		$file = $this->importsName();

		if (! is_file($file)) {
			// Imports register file doesn't exist
			$imports = NULL;
		}
		else if (($imports = file_get_contents($file)) === FALSE) {
			throw new \Exception("Can't read imports from register file `{$file}`");
		}
		else {
			$imports = json_decode($imports, TRUE);
		}

		return $imports;
	}

	/**
	 * Save imports to register file
	 *
	 * @param array  $imports
	 */
	public function importsSave($imports)
	{
		$file = $this->importsName();

		if (file_put_contents($file, json_encode($imports)) === FALSE) {
			throw new \Exception("Can't write imports to register file `{$file}`");
		}
	}
}
