<?php
/*
	Base for the user's app controller.
	-	Defines main application's layout
	-	Defines action methods caller with 'before', 'action', 'after' triada
*/
namespace Micro;

class Controller
{
	// The name of the layout view 
	public $layout = '';	// any string will cause constructor to create layout view with this name and without data

	// Current action for use anywhere
	public static $action;

	/**
	 * Make controller method name for a given action and check it's existence in the user's controller
	 * 
	 * @param  string
	 * @return string | NULL
	 */
    public static function method($action)
    {
		// Save this action for further use
		static::$action = $action;

		$method = 'action'.ucfirst($action === '/' ? 'index' : $action);

		return method_exists(static::class, $method) ? $method : NULL;
    }

	/**
	 * Call given controller action
	 * If action doesn't exist action 'error404' will be called
	 * Throws an exception if action 'error404' is undefined in the controller
	 *
	 * @param  string
	 * @param  array
	 * @return any
	 */
    public static function call($action, $param = NULL)
    {
		// Make controller method
		if (is_null($method = static::method($action))) {
			if (is_null($method = static::method('error404'))) {
				throw new \Exception("Can't found controller action for `{static::$action} - Page not found`.");
			}
		}

		// Make controller instance
		$controller = new static();

		// Call controller method triada
		$controller->before();
		$response = $controller->$method($param);
		$response = $controller->after($response);

		return $response;
    }

    /**
     * Create a new controller instance with layout view.
     *
     * @return void
     */
    public function __construct()
    {
		// Setup the empty layout view used by the controller.
		if (is_string($this->layout)) {
			$this->layout = new View($this->layout);
		}
    }

	/*
	 * Before action - prepare for action
	 *
	 * @return void
	 */
	public function before()
	{
	}

	/*
	 * After action - change response before return
	 *
	 * @param  any
	 * @return any
	 */
	public function after($response)
	{
		return $response;
	}
}
