<?php

class Input
{
	public static function post($name = '')
	{
		if($name !== '') return isset($_POST[$name]) ? $_POST[$name] : null;

		return $_POST;
	}

	public static function get($name)
	{
		return isset($_GET[$name]) ? $_GET[$name] : null;
	}
}