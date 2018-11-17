<?php
require_once 'autoload.php';
require_once 'commonFuncs.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'config.php';

define('VIEW_PATH', __DIR__
							. DIRECTORY_SEPARATOR . 'front'
							. DIRECTORY_SEPARATOR . 'views'
							. DIRECTORY_SEPARATOR);

define('ROOT_DIR', __DIR__);

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

	public function reset()
	{
		if((new GameModel())->reset())
		{
			$this->redirect('');
		}
	}

	public function page404()
	{
		$this->render('page404');
	}
}

// заполняет склад имитируя таблицы БД(из $_POST)
// initStore();

$c = new Strategy();

switch (Input::get('page')) {
	case '': $c->index(); break;
	case 'init': $c->init(); break;
	case 'step': $c->step(); break;
	case 'reset': $c->reset(); break;
	default: $c->page404(); break;
}