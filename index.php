<?php
require_once 'autoload.php';
require_once 'commonFuncs.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'config.php';

define('VIEW_PATH', __DIR__
                            . DIRECTORY_SEPARATOR . 'views'
                            . DIRECTORY_SEPARATOR);

define('ROOT_DIR', __DIR__);

//$c1 = new Deck(array('coordinat' => '1:1', 'state' => '2'));
////$c2 = new Deck(array('name' => '9:1', 'state' => '2'));
//$c2 = $c1->addOffset('0:-1');
//
//echo '<pre>';
//var_dump($c2);
//exit;

class Strategy extends Base
{
    /**
     * Стартовая страница
     */
    public function index()
    {
        $this->render('index');
    }

    /**
     * Страница инициализации игроков
     */
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
                'player' => $player, // текущий игрок
                'fields' => $game->getFields(), // оба поля
                'minCoordinat' => $game->getMinCoordinat(),
                'maxCoordinat' => $game->getMaxCoordinat()
            ));
        }

        $this->render('init', array(
            'players' => $game->getPlayers(),
            'minCoordinat' => $game->getMinCoordinat(),
            'maxCoordinat' => $game->getMaxCoordinat()
        ));
    }

    /**
     * Страница поочередных шагов игроков
     */
    public function step()
    {
        $game = new GameModel();

//        если step вернет true игрок играет дальше
        if($game->step(Input::post('current_player_id'), Input::post('enemy_player_id'), Input::post('cell')))
        {
            $player = $game->getPlayer(Input::post('current_player_id'));
        }
        else
        {
            $player = $game->getPlayer(Input::post('enemy_player_id'));
        }
        
//        если конец игры переводим игрока на победную страницу
        if($game->isEnd(Input::post('enemy_player_id')))
        {
            $this->redirect('page=end&player_name=' . $player->getName() . '&player_id=' . $player->getId());
            exit;
        }

        $this->render('step', array(
            'player' => $player,
            'fields' => $game->getFields(),
            'minCoordinat' => $game->getMinCoordinat(),
            'maxCoordinat' => $game->getMaxCoordinat()
        ));
    }
    
    /**
     * Сброс состояния игры
     */
    public function reset()
    {
        if((new GameModel())->reset())
        {
            $this->redirect('');
        }
    }
    
    /**
     * Action страница завершения игры
     */
    public function end()
    {
        $game = new GameModel();
        $game->reset();
        
        $this->render('end', array(
            'playerId' => Input::get('player_id'),
            'playerName' => Input::get('player_name')
        ));
    }

    /**
     * 
     */
    public function page404()
    {
        $this->render('page404');
    }
}

// заполняет склад имитируя таблицы БД(из $_POST)
// initStore();

$c = new Strategy();

// :FIX переделать роутер
switch (Input::get('page')) {
    case '': $c->index(); break;
    case 'index': $c->index(); break;
    case 'init': $c->init(); break;
    case 'step': $c->step(); break;
    case 'reset': $c->reset(); break;
    case 'end': $c->end(); break;
    default: $c->page404(); break;
}