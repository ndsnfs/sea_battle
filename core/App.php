<?php

class App
{
	private $_state = array();

	public function __construct($config)
	{
		
	}

	public function __get($name)
	{
		if(array_key_exists($name, $this->_state))
		{
			return $this->_state[$name];
		}

		throw new Exception("Такого свойства не существует");
	}
}