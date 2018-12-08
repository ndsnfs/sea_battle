<?php

class Battle extends Base
{
    /**
     * Стартовая страница
     */
    public function index()
    {
        $this->render('index');
    }
    
    /**
     * Создает игрока и поле
     */
    public function create()
    {
        $game = new GameModel();
        
        if(!$game->initPlayer(Input::post('player_name'), Input::post('cell_status')))
        {
            $this->render('initError', array(
                'errors' => $game->getSelfErrors(),
                'playerName' => Input::post('player_name'),
                'field' => $game->getInitField(),
                'players' => $game->getPlayers()
            ));
        }
//        если игрок и поле созданы перенаправляем на страницу инициализации
        $this->redirect('r=battle/init');
    }

    /**
     * Страница инициализации игроков
     */
    public function init()
    {
        $game = new GameModel();

        // проверяем инициализирована ли игра
        if($game->isInit())
        {
            $gamers = $game->getFirstStep();
            $current = $gamers['current'];
            $enemy = $gamers['enemy'];
            
            $this->render('step', [
                'currentPlayerField' => $game->getField($current->getId()), 
                'enemyPlayerField' => $game->getField($enemy->getId()),
                'current' => $current,
                'enemy' => $enemy,
            ]);
        }
        
        $this->render('init', array(
            'field' => $game->getEmptyField(),
            'players' => $game->getPlayers()
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
            /* @var Текущий игрок */
            $current = $game->getPlayer(Input::post('current_player_id'));
            $enemy = $game->getPlayer(Input::post('enemy_player_id'));
        }
        else
        {
            $current = $game->getPlayer(Input::post('enemy_player_id'));
            $enemy = $game->getPlayer(Input::post('current_player_id'));
        }
        
//        если конец игры переводим игрока на победную страницу
        if($game->isEnd(Input::post('enemy_player_id')))
        {
            $this->redirect('r=battle/end&player_name=' . $current->getName() . '&player_id=' . $current->getId());
            exit;
        }

        $this->render('step', [
            'currentPlayerField' => $game->getField($current->getId()), 
            'enemyPlayerField' => $game->getField($enemy->getId()),
            'current' => $current,
            'enemy' => $enemy,
        ]);
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