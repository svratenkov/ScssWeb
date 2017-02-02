<?php
/*
	Compiler output history stored in the session
	Compiler output is a set (history) of all recent compilation's messages.
*/
namespace App;

class Output
{
	// Closure for decorating compiler output message to be distinquished between other messages
	public static $decorator;

	/**
	 * Decorate new compiler output message to be distinquished between other messages
	 * 
	 * @param string $msg   - compiling results array
	 * @return string - output message
	 */
	public static function decorate($msg)
	{
		return is_callable(static::$decorator) ? call_user_func(static::$decorator($msg)) : '<p>'.$msg.'</p>';
	}

	/**
	 * Append new compiler output message to the history
	 * Returns all the history with appended message
	 * 
	 * @param string $msg   	- compile message
	 * @param string $greeting	- first compile message if history is empty
	 * @return string - new output history
	 */
	public static function append($msg, $greeting = NULL)
	{
		$history = static::get() ?: static::decorate($greeting);

		$output = $history.static::decorate($msg);

		return static::set($output);
	}

	/**
	 * Get compiler output history
	 * @return string	- output history
	 */
	public static function get()
	{
		return ! empty($_SESSION['output']) ? $_SESSION['output'] : '';
	}

	/**
	 * Set & save new compiler output history
	 * @param string $output 	- new output history
	 * @return string			- new output history
	 */
	public static function set($output = '')
	{
		return $_SESSION['output'] = $output;
	}
}