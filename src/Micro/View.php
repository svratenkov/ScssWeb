<?php
/*
	View - Template with vars
*/
namespace Micro;

class View
{
	public static $dir;			// View templates dir

	public $name;				// View template file name relative to static::$dir
	public $data;				// View template vars
	public $file;				// View template abs file name

	/*
		Constructor
	*/
	public function __construct($name = NULL, $data = [])
	{
		$this->name($name);
		$this->with($data);
	}

	/**
	 * Set the view name
	 *
	 * @param  string  $name
	 * @return $this
	 */
	public function name($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Add a key / value pair to the view data.
	 *
	 * Bound data will be available to the view as variables.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return View
	 */
	public function with($key, $value = null)
	{
		if (is_array($key))
		{
			$this->data = array_merge((array) $this->data, $key);
		}
		else
		{
			$this->data[$key] = $value;
		}

		return $this;
	}

	/**
	 * Get the array of view data for the view instance.
	 *
	 * @return array
	 */
	public function data()
	{
		$data = $this->data;

		// All nested views are evaluated before the main view
		foreach ($data as $key => $value) 
		{
			if ($value instanceof View)
			{
				$data[$key] = $value->render();
			}
		}

		return $data;
	}

	/**
	 * Get/Set the view directory
	 *
	 * @param  string  $name
	 * @return $this
	 */
	public static function dir($name = NULL)
	{
		if (! is_null($name)) {
			// Set views dir
			if (! ($dir = realpath($name))) {
				$cwd = getcwd();
				throw new \Exception("Can't find views directory `{$name}` in cwd: `{$cwd}`");
			}
			static::$dir = $dir.DIRECTORY_SEPARATOR;
		}

		// Return views dir
		return static::$dir;
	}

	/**
	 * Render view.
	 *
	 * @return string
	 */
	public function render()
	{
		$this->file = static::$dir.$this->name.'.php';

		if (! file_exists($this->file))
		{
			$dir = static::$dir;
			throw new \Exception("Can't find view template `{$this->file}` in views dir `{$dir}`");
		}

		// Add view data to current scope and evaluate the template code
		ob_start();

		try {
			extract($this->data());
			include $this->file;
		}
		catch (\Exception $e) {
			ob_end_clean();
			throw $e;
		}

		return ob_get_clean();
	}

	/**
	 * Magic Method for handling dynamic data access.
	 */
	public function __get($key)
	{
		return $this->data[$key];
	}

	/**
	 * Magic Method for handling the dynamic setting of data.
	 */
	public function __set($key, $value)
	{
		$this->data[$key] = $value;
	}
}
