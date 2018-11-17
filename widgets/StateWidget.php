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
		$p = $this->_store->getAll('players');
		if(!is_array($p) || count($p) === 0) return;
		$f = $this->_store->getAll('fields');
		$players = base64_encode(serialize($p));
		$fields = base64_encode(serialize($f));

		echo '<input type="hidden" name="players" value="'.$players.'">';
		echo '<input type="hidden" name="fields" value="'.$fields.'">';
	}
}