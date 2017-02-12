<?php
/*
	Application's controller.
	-	Defines main application's layout
	-	Defines action methods
	-	Defines default action for static pages undefined in the routes.php
	-	Defines actionError404 - Page not found

!!!	All page names for various SCSS projects begin with '$' to be distingvished from "normal" pages
!!!	All of them serves by one 'project' action with project name as parameter
!!!	This service done in the call() method of this class
*/
namespace App;
use Micro\Controller as MicroController;
use Micro\Request;
use Micro\View;

class Controller extends MicroController
{
	// HTML page titles
	public static $page_titles = [
		'/'			=> 'Home',
		'compile'	=> 'Compile',
		'watch'		=> 'Watch',
		'project'	=> 'Project',
		'error404'	=> 'Error404',
	//	'clearcache'=> 'ClearCache',
	//	'clear'		=> 'Clear',
	//	'stopwatch'	=> 'StopWatch',
	//	'ajax'		=> 'Ajax',
	];

	/**
	 * Call given controller action
	 *
     * Overloads the parent method
     * All page names for SCSS projects begin with '$' to be distingvished from "normal" pages 
     * Check for project's page names and call actionProject
     * 
	 * @param  string
	 * @param  array
	 * @return any
	 */
    public static function call($action, $param = NULL)
    {
		// Check for requeting project's page
		if (substr($action, 0, 1) === '$') {
			$project = substr($action, 1);
			$action = 'project';
		}
		else {
			$project = NULL;
		}

		return parent::call($action, $project);
    }

	/*
		Before action - prepare for action
		@return void
	*/
	public function before()
	{
		// Session handles some global vars: compiler output, active project name, ...
		session_start();

		// Activate recently used project OR redirect to home page with alert
		if (is_null(Project::activate()) AND Request::$query !== '/') {
			Request::redirect('/');
		}

		// Define application layout templates dir
		View::$dir = realpath('src/views');

		// Define empty app layout
		$this->layout
			->name('layout')
			->with([
				'icon'		=> 'grab.ico',
				'header'	=> View::make('header'),
				'content'	=> View::make(),
				'footer'	=> View::make('footer'),
			])
		;
	}

	/*
		After action - change response before return
	*/
	public function after($response)
	{
		// Add page titles
		$this->layout->with('title', static::$page_titles[static::$action].' - ScssWeb Watcher');

		// Define project name tabs in the header
		$this->layout->header->with([
			'projects'	=> array_keys((array) Project::$projects),
			'active'	=> Project::$active ? Project::$active->name : '',
			'watching'	=> Dispatcher::$watching,
		]);

		// Render & return
		return $this->layout->render();
	}

	/**
	 * Home page action
	 */
	public function actionIndex()
	{
		$this->layout->content
			->name('home')
			->with('alert_no_projects', is_null(Project::$active))
		;
	}

	/**
	 *	Compile action
	 */
	public function actionCompile()
	{
		$results = Dispatcher::rebuild();

		$msg = Output::append(Dispatcher::outputFormat($results), 'Welcome to ScssWeb Compiler!');

		$this->layout->content
			->name('compile')
			->with('output', $msg)
		;
	}

	/**
	 *	Clear output action
	 */
	public function actionClear()
	{
		Output::set();
		Output::append('Welcome to ScssWeb Compiler!');

		Request::redirect('compile');
	}

	/**
	 *	Watch action
	 */
	public function actionWatch()
	{
		// Set Watch mode flag and disable all other services in the header buttons
		Dispatcher::$watching = TRUE;

		// Make first {forced} compile
		$results = Dispatcher::rebuild();	//TRUE);

		// Replace previous output with starting watch message
		Output::set();
		$greeting = "Welcome to ScssWeb Watcher!<br/>Watching {$results['scss_file']}";
		$msg = Output::append(Dispatcher::outputFormat($results), $greeting);

		// Add page script with autorunning watch loop
		$this->layout->content
			->name('compile')
			->with([
				'output'	=> $msg,
				'delay'		=> '1000',					// watcher delay in micro sec's
				'min_delay'	=> '500',					// watcher min delay in micro sec's
				'url_ajax'	=> Request::url('ajax'),	// watcher ajax request url
			])
		;
	}

	/**
	 *	StopWatch action
	 */
	public function actionStopWatch()
	{
		// Clear watcher output
		Output::set();

		// JS watching loop is stopped by onClick action in the Stop button
		// Go home...
		Request::redirect('/');
	}

	/**
	 *	Ajax Watch action
	 */
	public function actionAjax()
	{
		// Recompile it
		$results = Dispatcher::rebuild();

		$output = $results['css'] ? Output::decorate(Dispatcher::outputFormat($results)) : '';

		// Ajax return string to browser
		exit($output);
	}

	/**
	 *	Project action
	 */
	public function actionProject($project = NULL)
	{
		// Set new active project defined in the first segment
		if ($project) {
			Project::activate($project);
		}

		$this->layout->content
			->name('project')
			->with('active', Project::$active)
		;
	}

	/**
	 *	Clear current project cache dir
	 */
	public function actionClearCache()
	{
		// Clear current project cache dir
		Project::clearCache();

		// Redirect to active project details page
		Request::redirect('$'.Project::$active->name);
	}

	/**
	 * Error404 action - Page not found
	 */
	public function actionError404()
	{
		$this->layout->content
			->name('404')
			->with('uri', Request::$query)
		;
	}
}
