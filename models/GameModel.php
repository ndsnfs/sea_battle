<?php

class GameModel extends MainModel
{
    private $_initField = [];
    
    /**
     * @var array ошибки не относящиеся к валидации
     */
    private $_selfErrors = [];
    
    /*-- Правила игры --*/
    
    /**
     * @var int Максимальный размер поля
     */
    private static $_maxCoordinat = 9;

    /**
     * Виды кораблей которые могут быть в игре - "кол-во палуб" => "кол-во кораблей"
     * @var array
     */
    private static $_shipCntRule = array(1 => 4, 2 => 3, 3 => 2, 4 => 1);
    
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
     * @param string $currentPlayerId Строковое значеине ИД игрока делающего ход
     * @param string $enemyPlayerId Строковое значеине ИД соперника
     * @param string $cell Координаты ячейки по которой стреляют
     * @return bool true в случае попадания, false в случае промаха
     */
    public function step($currentPlayerId, $enemyPlayerId, $cell) // :bool
    {        
        $fields = $this->DB->getWhere('fields', array('player_id' => $enemyPlayerId));

        foreach ($fields as $row)
        {
            if($row['coordinat'] === $cell and $row['status'] == FieldModel::getShipCell()) // :FIX Не жесткое сравнение типов
            {
                $this->DB->replace('fields',
                    [
                        'player_id' => $enemyPlayerId,
                        'coordinat' => $cell
                    ],
                    [
                        'player_id' => $enemyPlayerId,
                        'coordinat' => $cell,
                        'status' => FieldModel::getWoundCell()
                    ]
                );
                
                return true;
            }
        }
            
        $this->DB->insert('fields',
            array(
                'player_id' => $enemyPlayerId,
                'coordinat' => $cell,
                'status' => FieldModel::getFailedCell()
            )
        );

        return false;
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
        $id = md5(time());
        $player = new PlayerModel(['playerId' => (string)$id, 'playerName' => (string)$playerName]);
        
        if(!$player->validate())
        {
            $this->_selfErrors = array_merge($this->_selfErrors, $player->validationErrors());
        }

//        Пробуем создать его поле
        $field = new FieldModel();
        $field->setShipsCnt(self::$_shipCntRule);
        $field->setMaxCoordinat(self::$_maxCoordinat);
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
        
//        Пробуем создать поле
        $dataInsertBatch = [];

        foreach($shipsCells as $coordinat => $status)
        {
            $dataInsertBatch[] = ['player_id' => $id, 'coordinat' => $coordinat, 'status' => $status];
        }
        
        $this->DB->transBegin();
        
        $isInsertedPlayer = $this->DB->insert('players', ['id' => (string)$id, 'name' => (string)$playerName]);
        $isInsertedField = $this->DB->insertBatch('fields', $dataInsertBatch);
        
        if($isInsertedPlayer && $isInsertedField)
        {
            $this->DB->transCommit();
            return TRUE;
        }
        
        $this->DB->transRollback();
        return FALSE;
    }
    
    /**
     * Создает пустое поле
     * @return void
     */
    public function getEmptyField()
    {
        $field = new FieldModel();
        $field->setMaxCoordinat(self::$_maxCoordinat);
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
        $players = $this->DB->getAll('players');

        foreach ($players as $p)
        {
            $arrTmp[] = new PlayerModel(['playerId' => $p['id'], 'playerName' => $p['name']]);
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
        $playerArr = $this->DB->getOne('players', ['id' => $playerId]);

        if($playerArr)
        {
            $playerObj = new PlayerModel(['playerId' => $playerArr['id'], 'playerName' => $playerArr['name']]);
            
            return $playerObj;
        }

        return null;
    }

    /**
     * Возвращает массив в котором все поля всех игроков
     *
     * @return array Массив из полей
     */
    public function getField($playerId)
    {
        $state = $this->DB->getWhere('fields', ['player_id' => $playerId]);
//        подготавливаем данные для добавления
        $tmp = [];
        
        foreach($state as $row)
        {
            $tmp[$row['coordinat']] = $row['status'];
        }

        $fieldModel = new FieldModel();
        $fieldModel->setShipsCnt(self::$_shipCntRule);
        $fieldModel->setMaxCoordinat(self::$_maxCoordinat);
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
     * 
     * @return bool
     */
    public function reset()
    {
        if($this->DB->clear('fields') && $this->DB->clear('players'))
        {
            return true;
        }
        
        return false;
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
    
    /**
     * Проверяет окончена ли игра
     * @param string $enemyPlayerId Строковое ИД соперника
     * @return boolean
     */
    public function isEnd($enemyPlayerId)
    {
        $field = $this->DB->getWhere('fields', array('player_id' => $enemyPlayerId));
        
//        :FIX тут добавить вставку в БД postgres
        
        foreach ($field as $row)
        {
//            если есть хотя бы одна ячейка с неподбитым кораблем значит игра не закончена
            if($row['status'] == FieldModel::getShipCell()) // :FIX не жесткое сравнение
            {
                return false;
            }
        }
        
        return true;
    }
}