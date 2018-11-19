<?php

class GameModel extends MainModel
{
	/**
	 * максимальное кол-во игроков
	 */
	private static $_maxCntPlayers = 2;

	private $_FIELD = null;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * возвращает максимальное значение координаты(x или y)
	 */
	public function getMaxCoordinat()
	{
		return FieldModel::getMaxCoordinat();
	}

	/**
	 * возвращает минимальное значение координаты(x или y)
	 */
	public function getMinCoordinat()
	{
		return FieldModel::getMinCoordinat();
	}

	/**
	 * 
	 */
	public function step($currentPlayerId, $enemyPlayerId, $cell) //:bool
	{
		$result = false;
		$fields = $this->DB->getWhere('fields', array('player_id' => $enemyPlayerId));

		foreach ($fields as &$row)
		{
			foreach ($row['field_state'] as &$arr)
			{
				if(array_key_exists($cell, $arr))
				{
					if($arr[$cell] === FieldModel::getEmptyCell())
					{
						$arr[$cell] = FieldModel::getFailedCell();
						$this->DB->update('fields', array('field_state' => $row['field_state']), array('player_id' => $enemyPlayerId));
					}
					elseif($arr[$cell] === FieldModel::getShipCell())
					{
						$result = true;
						$arr[$cell] = FieldModel::getWoundCell();
						$this->DB->update('fields', array('field_state' => $row['field_state']), array('player_id' => $enemyPlayerId));
					}
					else
					{
						throw new Exception("Error Processing Request");
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Инициализация нового игрока
	 *
	 * @param String $playerName Имя игрока
	 * @param Array $celllsState Состояние поля игрока
	 */
	public function initPlayer($playerName, $cellsState)
	{
		$field = new FieldModel();
		$field->changeState($cellsState);
		$id = md5(time());
		$this->DB->insert('players', array('id' => $id, 'name' => (string)$playerName));
		$this->DB->insert('fields', array('player_id' => $id, 'field_state' => $field->getState()));
	}

	/**
	 * возвращает игроков
	 * 
	 * @return Array Массив игроков
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
	 * возвращает игрока
	 * 
	 * @param int $playerId ИД игрока
	 *
	 * @return object|null Объект игрок или null
	 */
	public function getPlayer($playerId)
	{
		$player = $this->DB->getOne('players', array('id' => $playerId));
		
		if($player)
		{
			return new PlayerModel($player['id'], $player['name']);
		}

		return null;
	}

	/**
	 * Возвращает массив в котором все поля всех игроков
	 *
	 * @return Array Массив из полей
	 */
	public function getFields()
	{
		return $this->DB->getAll('fields');
	}

	/**
	 * Возвращает случайного игрока
	 * 
	 * @return object Объект игрок
	 */
	public function getFirstStep()
	{
		$players = $this->getPlayers();
		$randKey = array_rand($players, 1);
		return $players[$randKey];
	}

	/**
	 * Стирает данные о игре
	 * 
	 * @return bool
	 */
	public function reset()
	{
		$this->DB->clear('players');
		$this->DB->clear('fields');

		return true;
	}

	/**
	 * Проверяет инициализирована игра или нет
	 * по кол-ву игроков, если их 2 тогда игра инициализирована
	 *
	 * @return bool
	 */
	public function isInit()
	{
		$p = $this->DB->getAll('players');

		if(self::$_maxCntPlayers === count($p))
		{
			return true;
		}

		return false;
	}
}