<?php

class PlayerModel
{
	private $_id = '';
	private $_name = '';

	public function __construct($playerId, $playerName)
	{
		$this->_id = $playerId;
		$this->_name = $playerName;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function getName()
	{
		return $this->_name;
	}
}