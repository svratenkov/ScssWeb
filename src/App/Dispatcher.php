<?php
/**
 * Project compiler
 */
namespace App;

/**
 * SCSS Cached compiler
 */
class Dispatcher
{
	// Watch mode flag - disables all other services
	public static $watching = FALSE;

    /**
     * Next ReBuild scss file with checking if input root and import cscc files are newer then output css
     *
     * @param string $in		-	path to input scss file
     * @param string $out		-	path to output css file
     * @param array $imports	-	array of paths to previously imported scss files
     * 
     * @return array|null		-	array of compilation results or NULL if no changes
     */
	public static function rebuild($project = NULL, $forced = FALSE)
	{
		if (is_null($project)) {
			$project = Project::$active;
		}

		$in = $project->scss_file;
		$out = $project->css_file;

		// Make return array
		$results = [
			'scss_file'	=> $in,
			'css_file'	=> $out,
			'css'		=> NULL,
			'elapsed'	=> 0,
			'compiled'	=> [],
			'imported'	=> [],
		];
		clearstatcache();

		$start = microtime(true);

		$compiler = new Compiler($project->cache_dir, $project->css_style);

		$css = $compiler->compileChecked($in, $out, $forced);

		$elapsed = round(microtime(true) - $start, 4);

		if (! is_null($css))
		{
			// Output css file signature
			$prefix = static::cssSignature($elapsed);

			// Save output css to file
			static::cssWrite($out, $prefix.$css);

			// Update return array and active project
			$results['compiled'] = $compiler->getCompiledFiles();
			$results['imported'] = $compiler->getImportedFiles();
		}

		$results['elapsed'] = $elapsed;
		$results['css'] = $css;

		return $results;
	}

	/**
	 * Format compiler output message from the compiling results array
	 * 
	 * @param string $results   - compiling results array
	 * @return string - output message
	 */
	public static function outputFormat($results)
	{
		$now = strftime('%c');

		if ($results['css']) {
			$msg = "Changes detected at {$now}<br/>";
			foreach ($results['compiled'] as $file) {
				$msg .= 'Compiled: '.$file.'<br/>';
			}
			$msg .= "Build {$results['css_file']} ({$results['elapsed']}s)";
		}
		else {
			$msg = "No changes detected at {$now} for {$results['scss_file']} ({$results['elapsed']}s)";
		}

		return $msg;
	}

    /**
     * Write css code into output css file
     *
     * @param string $out		-	path to output scss file
     * @param string $css		-	css code
     *
     * @return none
     * 
     * @throws \Exception
     */
	public static function cssWrite($out, $css)
	{
		if (! file_put_contents($out, $css)) {
			throw new \Exception("Can't save compiled css code to {$out}");
		}
	}

	public static function cssSignature($elapsed)
	{
		$time = strftime('%c');
		return "/* Compiled by CachedCompiler based on Leafo scssphp v0.6.2 on {$time} ({$elapsed}s) */\n\n";
	}
}
