<?php
// Временно!!!
set_include_path(get_include_path()
									. PATH_SEPARATOR . 'libs'
									. PATH_SEPARATOR . 'core'
									. PATH_SEPARATOR . 'models'
									. PATH_SEPARATOR . 'widgets'
									. PATH_SEPARATOR . 'core'
										. DIRECTORY_SEPARATOR . 'drivers'
										. DIRECTORY_SEPARATOR . 'db'
									. PATH_SEPARATOR . 'core'
										. DIRECTORY_SEPARATOR . 'interfaces');
function __autoload($class)
{
	require_once $class . '.php';
}