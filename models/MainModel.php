<?php

class MainModel
{
	public $DB = null;

	public function __construct()
	{
		$this->DB = new DB();
	}
}