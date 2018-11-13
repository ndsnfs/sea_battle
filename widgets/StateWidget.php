<?php

class StateWidget
{
	private $_store = null;

	public function __construct()
	{
		$this->_store = Store::getInstance();
	}

	public function draw()
	{
		if(!is_array($this->_store->players) || count($this->_store->players) === 0) return;
		$players = base64_encode(serialize($this->_store->players));
		$fields = base64_encode(serialize($this->_store->fields));

		echo '<input type="hidden" name="players" value="'.$players.'">';
		echo '<input type="hidden" name="fields" value="'.$fields.'">';
	}
}