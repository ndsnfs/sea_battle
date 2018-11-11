<?php
require_once 'autoload.php';
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
}

class Controller extends MainController
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

		if($game->step(Input::post('current_player_id'),Input::post('enemy_player_id'),Input::post('cell')))
		{

		}


	}

	public function page404()
	{
		$this->render('page404');
	}
}

function initStore()
{
	// если есть post players и  fields
	// заполняем импровизируемую талицу
	// пришедшими игроками и их состоянием полей
	if(Input::post('players') && Input::post('fields'))
	{
		$players = unserialize(base64_decode(Input::post('players')));
		$fields = unserialize(base64_decode(Input::post('fields')));
		
		$store = Store::getInstance();

		foreach ($players as $p)
		{
			$store->players = $p;
		}

		foreach ($fields as $f)
		{
			$store->fields = $f;
		}
	}
}

// заполняет склад имитируя таблицы БД(из $_POST)
initStore();

$c = new Controller();

switch (Input::get('page')) {
	case '': $c->index(); break;
	case 'init': $c->init(); break;
	case 'step': $c->step(); break;
	default: $c->page404(); break;
}