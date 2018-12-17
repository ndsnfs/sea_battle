<?php

class GameModel extends MainModel
{
    /**
     * Уникальный номер игры из двух человек
     * @var string
     */
    private $_number;
    
    private $_initField = [];
    
    /**
     * @var array ошибки не относящиеся к валидации
     */
    private $_selfErrors = [];
    
    
    
    /*-- Правила игры --*/
    
    /**
     * Максимальное кол-во игроков
     */
    private static $_maxCntPlayers = 2;

    /*-- Правила игры END --*/

    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Устанавливает уникальный номер игры из двух игроков
     * @param string $number
     */
    public function setNumber(string $number)
    {
        $this->_number = $number;
    }
    
    /**
     * Возвращает поле, каким бы оно не было
     */
    public function getInitField()
    {
        return $this->_initField;
    }
    
    /**
     * 
     */
    public function getSelfErrors()
    {
        return $this->_selfErrors;
    }

    /**
     * Ход игрока
     * 
     * @param string $enemyPlayerId Строковое значеине ИД соперника
     * @param string $cell Координаты ячейки по которой стреляют
     * @return bool true в случае попадания, false в случае промаха
     */
    public function step($enemyPlayerId, $cell) // :bool
    {
//        найти поле соперника
        $this->DB->join('battle_fields', 'battle_fields.game_id=battle_games.id');
        $field = $this->DB->getOne('battle_games', ['battle_games.player_id' => $enemyPlayerId], ['battle_fields.id']);
        
//        попробовать найти хотя бы одну ячейку с "неподбитой" палубой
        $shipCells = $this->DB->getOne('battle_cells', [
            'field_id' => $field['id'],
            'coordinat' => $cell,
            'state' => FieldModel::getShipCell()
        ]);
        
        $state = FieldModel::getFailedCell();
        $result = FALSE;
        
//        Если корабль найден - обновляем state на "подбит" 
        if(count($shipCells) > 0)
        {
            $state = FieldModel::getWoundCell();
            $result = TRUE;
        }
         
        $this->DB->replace('battle_cells', [
                'field_id' => $field['id'],
                'coordinat' => $cell,
                'state' => $state
            ], [
                'field_id' => $field['id'],
                'coordinat' => $cell,
            ]
        );

        return $result;
    }

    /**
     * Инициализация нового игрока
     *
     * @param string $playerName Имя игрока
     * @param array $shipsCells Состояние поля игрока
     */
    public function initPlayer($playerName, $shipsCells)
    {
//        пробуем создать игрока
        $player = new PlayerModel(['playerName' => (string)$playerName]);
        
        if(!$player->validate())
        {
            $this->_selfErrors = array_merge($this->_selfErrors, $player->validationErrors());
        }

//        Пробуем создать его поле
        $field = new FieldModel();
        $field->createField($shipsCells);
        
        if(!$field->validate())
        {
            $this->_selfErrors = array_merge($this->_selfErrors, $field->validationErrors());
            $this->_selfErrors = array_merge($this->_selfErrors, $field->getAjacentCoordinats());
        }
        
        if(count($this->_selfErrors) > 0)
        {
//            сохраняем какое получилось поле
            $this->_initField = $field->FIELD;
            return FALSE;
        }
        
        $this->DB->transBegin();
        
//        Вставляем игрока
        $isInsertedPlayer = $this->DB->insert('battle_players', ['name' => $playerName]);
        $playerId = $this->DB->lastInsertId('auto_inc_battle_players');
        
//        Вставляем игру
        $game = $this->DB->insert('battle_games', ['number' => $this->_number, 'player_id' => $playerId]);
        $gameId = $this->DB->lastInsertId('auto_inc_battle_games');
        
//        Вставляем поле
        $isInsertedField = $this->DB->insert('battle_fields', ['game_id' => $gameId]);
        $fieldId = $this->DB->lastInsertId('auto_inc_battle_fields');
        
//        Готовим ячейки к вставке
        $dataInsertBatch = [];
        
        foreach($shipsCells as $coordinat => $state)
        {
            $dataInsertBatch[] = ['field_id' => $fieldId, 'coordinat' => $coordinat, 'state' => $state];
        }
        
        $isInsertedCells = $this->DB->insertBatch('battle_cells', $dataInsertBatch);
        $errCells = $this->DB->errorInfo();
        
        if($this->DB->hasErrors())
        {
            $this->DB->transRollback();
            return FALSE;
        }
        
        $this->DB->transCommit();
        return TRUE;
    }
    
    /**
     * Создает пустое поле
     * @return void
     */
    public function getEmptyField()
    {
        $field = new FieldModel();
        $field->createField();
        
        return $field->FIELD;
    }

    /**
     * Возвращает игроков
     * 
     * @return array Массив игроков
     */
    public function getPlayers()
    {
        $arrTmp = [];
        
        $cols = ['battle_players.name as player_name', 'battle_players.id as player_id'];
        $where = ['battle_games.number' => $this->_number];
        $this->DB->join('battle_players', 'battle_players.id = battle_games.player_id');
        $players = $this->DB->getWhere('battle_games', $where, $cols);
        
        foreach ($players as $p)
        {
            $arrTmp[] = new PlayerModel(['playerId' => $p['player_id'], 'playerName' => $p['player_name']]);
        }

        return $arrTmp;
    }

    /**
     * возвращает игрока
     * 
     * @param int $playerId ИД игрока
     * @return object|null Объект игрок или null
     */
    public function getPlayer($playerId)
    {
        $playerArr = $this->DB->getOne('battle_players', ['id' => $playerId]);
        
        if(!$playerArr)
        {
            return NULL;
        }

        if($playerArr)
        {
            $playerObj = new PlayerModel(['playerId' => $playerArr['id'], 'playerName' => $playerArr['name']]);
            
            return $playerObj;
        }

        return NULL;
    }

    /**
     * Возвращает массив в котором все поля всех игроков
     *
     * @return array Массив из полей
     */
    public function getField($playerId)
    {
        $cols = ['battle_cells.coordinat', 'battle_cells.state'];
        $where = ['battle_games.player_id' => $playerId];
        
        $this->DB->join('battle_games', 'battle_games.id=battle_fields.game_id');
        $this->DB->join('battle_cells', 'battle_cells.field_id=battle_fields.id');
        $state = $this->DB->getWhere('battle_fields', $where, $cols);
        
//        подготавливаем данные для добавления
        $tmp = [];
        
        foreach($state as $row)
        {
            $tmp[$row['coordinat']] = $row['state'];
        }

        $fieldModel = new FieldModel();
        $fieldModel->createField($tmp);
        
        return $fieldModel->FIELD;
    }

    /**
     * Возвращает массив из игрока и соперника
     * 
     * @return array
     */
    public function getFirstStep()
    {
        $players = $this->getPlayers();
        $rand = rand(0, (count($players)-1));
        
        $counter = 0;
        $result = ['current' => NULL, 'enemy' => NULL];
        
        foreach ($players as $player)
        {
            if($rand === $counter)
            {
                $result['current'] = $player;
            }
            else
            {
                $result['enemy'] = $player;
            }
            
            $counter++;
        }
        
        return $result;
    }

    /**
     * Стирает данные о игре
     * @param int Ид игрока завершившего игру
     * @return bool
     */
    public function reset()
    {
        $playersRows = $this->DB->getWhere('battle_games', ['number' => $this->_number]);
        
        $this->DB->transBegin();
        
        foreach ($playersRows as $playerRow)
        {
            $this->DB->delete('battle_players', ['id' => $playerRow['player_id']]);
        }
        
        if($this->DB->hasErrors())
        {
            $this->DB->transRollback();
            htmlDebug($this->DB->errorInfo(), 'dump');
            return FALSE;
        }
        
        $this->DB->transCommit();
        return TRUE;
        
    }

    /**
     * Проверяет инициализирована игра или нет
     * по кол-ву игроков, если их 2 тогда игра инициализирована
     *
     * @return bool
     */
    public function isInit()
    {
        $p = $this->DB->getWhere('battle_games', ['number' => $this->_number]);

        if(self::$_maxCntPlayers === count($p))
        {
            return true;
        }

        return false;
    }
    
    /**
     * Проверяет окончена ли игра
     * @param string $enemyPlayerId Строковое ИД соперника
     * @return boolean
     */
    public function isEnd($enemyPlayerId)
    {
        $cols = ['battle_cells.state'];
        $where = ['battle_games.player_id' => (int)$enemyPlayerId, 'battle_cells.state' => FieldModel::getShipCell()];
        $this->DB->join('battle_fields', 'battle_fields.game_id=battle_games.id');
        $this->DB->join('battle_cells', 'battle_cells.field_id=battle_fields.id');
        $field = $this->DB->getOne('battle_games', $where, $cols);
        
        if($this->DB->hasErrors())
        {
//            :FIX Модель бросает исключение
//            :FIX Возвращаемое значение может быть не только bool, но и ошибка
            throw new Exception('Пофикси ошибки');
        }
        
//        Если массив не пустой
        if(!empty($field))
        {
            return false;
        }
        
        return true;
    }
}