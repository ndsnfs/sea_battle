<?php

class GameModel extends MainModel
{
	private static $_maxCntPlayers = 2;
	private $_playersCnt = 0;

	public function __construct()
	{
		parent::__construct();
		$this->_playersCnt = count($this->DB->getAll('players'));
	}

	/**
	 * 
	 */
	public function step($currentPlayerId, $enemyPlayerId, $cell) //:bool
	{
		$fields = $this->DB->getWhere('fields', array('player_id' => $enemyPlayerId));

		foreach ($fields as $row)
		{
			foreach ($row['field_state'] as $arr)
			{
				if(array_key_exists($cell, $arr))
				{
					echo 111;
					exit;
				}
			}
		}
	}

	/**
	 * данный метод не знает есть ли что в свойствах users и fields
	 */
	public function initPlayer($playerName, $cellsState)
	{
		$field = new FieldModel();
		$field->changeState($cellsState);
		$id = md5(time());
		$this->DB->insert('players', array('id' => $id, 'name' => (string)$playerName));
		$this->DB->insert('fields', array('player_id' => $id, 'field_state' => $field->getState()));
		$this->_playersCnt++;
	}

	/**
	 * возвращает игроков
	 */
	public function getPlayers()
	{
		$arrTmp = array();
		$players = $this->DB->getAll('players');

		foreach ($players as $p)
		{
			$arrTmp[] = new PlayerModel($p['id'], $p['name']);
		}

		return $arrTmp;
	}

	/**
	 * 
	 */
	public function getFields()
	{

		return $this->DB->getAll('fields');
	}

	/**
	 * назначает рандомному игроку ход
	 */
	public function getFirstStep()
	{
		$players = $this->getPlayers();
		$randKey = array_rand($players, 1);
		return $players[$randKey];
	}

	/**
	 * проверяет инициализирована игра или нет
	 * по кол-ву игроков, если их 2 тогда игра инициализирована
	 */
	public function isInit()
	{
		if(self::$_maxCntPlayers === $this->_playersCnt)
		{
			return true;
		}

		return false;
	}
}