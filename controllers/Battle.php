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
     * Страница инициализации игроков
     */
    public function init()
    {        
        if(!App::$serv->session->has('gameNumber'))
        {
            App::$serv->session->set('gameNumber', md5(time()));
            $this->redirect('r=battle/init');
        }
        
        $game = new GameModel();
        $game->setNumber(App::$serv->session->get('gameNumber'));
        
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
     * Создает игрока и поле
     */
    public function create()
    {
        $game = new GameModel();
        
        if(!App::$serv->session->get('gameNumber'))
        {
            throw new Exception('Пофикси ошибки');
        }
        
        $game->setNumber(App::$serv->session->get('gameNumber'));
        
        if(!$game->initPlayer(App::$serv->input->post('player_name'), App::$serv->input->post('cell_status')))
        {
            $this->render('initError', [
                'errors' => $game->getSelfErrors(),
                'playerName' => App::$serv->input->post('player_name'),
                'field' => $game->getInitField(),
                'players' => $game->getPlayers()
            ]);
        }
//        если игрок и поле созданы перенаправляем на страницу инициализации
        $this->redirect('r=battle/init');
    }

    /**
     * Страница поочередных шагов игроков
     */
    public function step()
    {
        if(!App::$serv->session->has('gameNumber'))
        {
            throw new Exception('Пофиксить ошибку');
        }
        
        $game = new GameModel();
        $game->setNumber(App::$serv->session->get('gameNumber'));

//        если step вернет true игрок играет дальше
        if($game->step(App::$serv->input->post('enemy_player_id'), App::$serv->input->post('cell')))
        {
            $current = $game->getPlayer(App::$serv->input->post('current_player_id'));
            $enemy = $game->getPlayer(App::$serv->input->post('enemy_player_id'));
        }
        else
        {
            $current = $game->getPlayer(App::$serv->input->post('enemy_player_id'));
            $enemy = $game->getPlayer(App::$serv->input->post('current_player_id'));
        }
        
//        если конец игры переводим игрока на победную страницу
        if($game->isEnd(App::$serv->input->post('enemy_player_id')))
        {
//            Сбрасываем состояние игры
            if(!$game->reset())
            {
                throw new Exception('Пофиксить ошибку');
            }
            
            $this->redirect('r=battle/end&player_name=' . $current->getName() . '&player_id=' . $current->getId());
        }

        $this->render('step', [
            'currentPlayerField' => $game->getField($current->getId()), 
            'enemyPlayerField' => $game->getField($enemy->getId()),
            'current' => $current,
            'enemy' => $enemy,
        ]);
    }
    
    /**
     * Сброс состояния игры, может делать любой из игроков
     */
    public function exit_from()
    {
        if(!App::$serv->session->has('gameNumber'))
        {
            throw new Exception('Пофиксить ошибку');
        }
        
        $game = new GameModel();
        $game->setNumber(App::$serv->session->get('gameNumber'));
        
        if($game->reset())
        {
            $this->redirect('');
        }
        else
        {
            throw new Exception('Пофиксить ошибку');
        }
    }
    
    /**
     * Action страница завершения игры
     */
    public function end()
    {        
        $this->render('end', array(
            'playerId' => App::$serv->input->get('player_id'),
            'playerName' => App::$serv->input->get('player_name')
        ));
    }
    
    public function page404()
    {
        $this->render('page404');
    }
}