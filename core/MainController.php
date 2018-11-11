<?php

class MainController
{
	public function render($file, Array $data = array())
	{
		if($data) extract($data);

		require_once VIEW_PATH . $file . 'View.php';
		exit;
	}

	public function redirect($queryString)
	{
		header('Location: ?' . $queryString);
	}
}