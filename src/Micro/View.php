<?php
/*
	Вьюха - шаблон (php файл) с переменными
*/
namespace Micro;

class View
{
	public static $dir;			// Директорий шаблонов вьюх

	public $name;				// Имя шаблона вьюхи относительно static::$dir
	public $data;				// Данные (переменные шаблона) вьюхи - наследуются от Container

	/*
		Статическая фабрика вьюхи
	*/
	public static function make($name = NULL, $data = [])
	{
		return new static($name, $data);
	}

	/*
		Конструктор вьюхи
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

		// All nested views and responses are evaluated before the main view.
		// This allows the assets used by nested views to be added to the
		// asset container before the main view is evaluated.
		foreach ($data as $key => $value) 
		{
			if ($value instanceof View)
			{
				$data[$key] = $value->render();
			}
		}

		return $data;
	}

	/*
		Создать вложенную вьюху как значение переменной $var
	*/
	public function nest($var, $name = NULL, $data = [])
	{
		$view = static::make($name, $data);

		// Цепочечный возврат себя
		return $this->with($var, $view);
	}

	/*
		Рендеринг текста шаблона с переменными
		Сначала пробует шаблон Blade, затем - PHP
		Возвращает HTML строку
	*/
	public function render()
	{
		$_file_ = static::$dir.DIRECTORY_SEPARATOR.str_replace('.', DIRECTORY_SEPARATOR, $this->name).'.php';

		// интерпретируем PHP шаблон
		if (file_exists($_file_))
		{
			ob_start();

			try {					// Load the view within the current scope
				// Единичная загрузка без буферизации - инклудим в контексте данных
				extract($this->data());
				include $_file_;
			}
			catch (\Exception $e) {
				ob_end_clean();		// Delete the output buffer
				throw $e;			// Re-throw the exception
			}

			return ob_get_clean();
		}
		else
		{
			throw new \Exception("Can't find template `{$_file_}`");
			return "<p>Can't find view template `{$_file_}`</p>";
		}
	}

	/*
		Магическое преобразование в строку
		!!!! C обработкой исключений - они запрещены в __toString()
	*/
	public function __toString()
	{
		try {
			return $this->render();
		}
		catch(\Exception $e) {
			// !!!! the __toString method isn't allowed to throw exceptions, SO we turn them into NULL
			// Возврат НЕ string приведет к ErrorException [ 4096 ]: Method __CLASS__::__toString() must return a string value
			return;

			// Fatal error - здесь можно??? передать свое сообщение об ошибке
		//	trigger_error(Error::$user_msg.$e->getMessage().$e->getTraceAsString());
		}
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
