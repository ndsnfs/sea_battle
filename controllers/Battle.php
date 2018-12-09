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
        
        if(!$game->initPlayer(App::$serv->input->post('player_name'), App::$serv->input->post('cell_status')))
        {
            $this->render('initError', array(
                'errors' => $game->getSelfErrors(),
                'playerName' => App::$serv->input->post('player_name'),
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
        $currentId = App::$serv->input->post('current_player_id');
        $enemyId = App::$serv->input->post('enemy_player_id');

//        если step вернет true игрок играет дальше
        if($game->step($currentId, $enemyId, App::$serv->input->post('cell')))
        {
            /* @var Текущий игрок */
            $current = $game->getPlayer($currentId);
            $enemy = $game->getPlayer($enemyId);
        }
        else
        {
            $current = $game->getPlayer($enemyId);
            $enemy = $game->getPlayer($currentId);
        }
        
//        если конец игры переводим игрока на победную страницу
        if($game->isEnd($enemyId))
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
            'playerId' => App::$serv->input->get('player_id'),
            'playerName' => App::$serv->input->get('player_name')
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