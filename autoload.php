<?php
set_include_path(get_include_path()
									. PATH_SEPARATOR . 'libs'
									. PATH_SEPARATOR . 'core'
									. PATH_SEPARATOR . 'models'
									. PATH_SEPARATOR . 'widgets');
function __autoload($class)
{
	require_once $class . '.php';
}