<?php

$conf = array();
require_once 'autoload.php';
require_once 'commonFuncs.php';
require_once 'config/db.php';

define('VIEW_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'front' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);

/**
 *  класс Store имитирует хранилище
 */
class Store
{
	private static $_instance = null;

	private $_players = array(); // array(array('id' => ..., 'name' => '...'), array('id' => ..., 'name' => '...'))
	private $_fields = array(); // array(array('player_id' => ..., 'field_state' => array(array('a:1' => 1, ...), ...), ...), ...)

	private function __construct(){}
	private function __clone(){}
	
	public static function getInstance()
	{
		if(self::$_instance === null)
		{
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function __get($propName)
	{
		$propName = '_' . $propName;

		if(property_exists(__CLASS__, $propName))
		{
			return $this->$propName;
		}

		return false;
	}

	// имитация insert
	public function __set($propName, $data)
	{
		$propName = '_' . $propName;
		if(property_exists(__CLASS__, $propName))
		{
			$this->$propName[] = $data;
			return true;
		}

		return false;
	}

	// имитация update
	public function update($table, Array $data, Array $where)
	{
		$propName = '_' . $table;
		if(!property_exists(__CLASS__, $propName))
		{
			throw new Exception("Таблица не найдена");
		}

		foreach ($this->$propName as &$row)
		{
			$cnt = 0;
			
			foreach ($where as $k => $v)
			{
				if($row[$k] == $v) $cnt++;
			}

			// если все условия в $where выполняются
			if($cnt === count($where))
			{
				// перебираем массив с обновлениями строки
				// и собственно обновляем эту строку
				foreach ($data as $field => $newValue)
				{
					if(array_key_exists($field, $row))
					{
						$row[$field] = $newValue;
					}
					else
					{
						throw new Exception("Поле не найдено");
					}
				}
			}
		}
	}
}

class Strategy extends Base
{
	public function index()
	{
		$this->render('index');
	}

	public function init()
	{
		$game = new GameModel();

		if(Input::post('player_name') && Input::post('cell_status'))
		{
			$game->initPlayer(Input::post('player_name'), Input::post('cell_status'));
		}

		// проверяем инициализирована ли игра
		if($game->isInit())
		{
			$player = $game->getFirstStep();
			$this->render('step', array(
				'players' => $game->getPlayers(),
				'player' => $player,
				'fields' => $game->getFields()
			));
		}

		$this->render('init', array(
			'players' => $game->getPlayers()
		));
	}

	public function step()
	{
		$game = new GameModel();

		// если step вернет true игрок играет дальше
		if($game->step(Input::post('current_player_id'), Input::post('enemy_player_id'), Input::post('cell')))
		{
			$player = $game->getPlayer(Input::post('current_player_id'));
		}
		else
		{
			$player = $game->getPlayer(Input::post('enemy_player_id'));
		}

		$this->render('step', array(
			'players' => $game->getPlayers(),
			'player' => new PlayerModel($player['id'], $player['name']),
			'fields' => $game->getFields()
		));
	}

	public function page404()
	{
		$this->render('page404');
	}
}

// заполняет склад имитируя таблицы БД(из $_POST)
initStore();

$c = new Strategy();

switch (Input::get('page')) {
	case '': $c->index(); break;
	case 'init': $c->init(); break;
	case 'step': $c->step(); break;
	default: $c->page404(); break;
}