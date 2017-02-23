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
	// any string will cause constructor to create layout view with this name and without data
	public $layout = '';

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

	/**
	 * Call given controller action
	 * If action doesn't exist action 'error404' will be called
	 * Throws an exception if action 'error404' is undefined in the controller
	 *
	 * @param  string
	 * @param  array
	 * @return any
	 */
    public function call($action, $params = [])
    {
		// Make controller method name
		if (is_null($method = $this->method($action))) {
			if (is_null($method = $this->method('error404'))) {
				throw new \Exception("Can't find controller action for `{$action}`.");
			}
		}

		// Call controller method triada
		$this->before();
		$response = $this->$method($params);
		$response = $this->after($response);

		return $response;
    }

	/**
	 * Make controller method name for a given action and check it's existence in the user's controller
	 * 
	 * @param  string
	 * @return string | NULL
	 */
    public function method($action)
    {
		$method = 'action'.ucfirst($action === '/' ? 'index' : $action);

		return method_exists($this, $method) ? $method : NULL;
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
