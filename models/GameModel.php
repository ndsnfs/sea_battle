<?php

class GameModel extends MainModel
{
    /*-- Правила игры --*/

    /**
     * Виды кораблей которые могут быть в игре - "кол-во палуб" => "кол-во кораблей"
     * @var array
     */
    private static $_shipCntRule = array(1 => 4, 2 => 3, 3 => 2, 4 => 1);
    
    /**
     * максимальное кол-во игроков
     */
    private static $_maxCntPlayers = 2;

    /*-- Правила игры END --*/

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Возвращает максимальное значение координаты(x или y)
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
                    array('player_id' => $enemyPlayerId, 'coordinat' => $cell),
                    array(
                        'player_id' => $enemyPlayerId,
                        'coordinat' => $cell,
                        'status' => FieldModel::getWoundCell()
                    )
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
        $playerModel = new PlayerModel(array('playerId' => (string)$id, 'playerName' => (string)$playerName));
        
        if(!$playerModel->validate())
        {
            debug($playerModel->validationErrors());
        }

//        Пробуем создать его поле
        $fieldModel = new FieldModel(array('fieldState' => $shipsCells));
        $fieldModel->setShipsCnt(self::$_shipCntRule);
        $fieldModel->createField();
        
        if(!$fieldModel->validate())
        {
            debug($fieldModel->validationErrors());
        }
        
        $this->DB->insert('players', array('id' => (string)$id, 'name' => (string)$playerName)); // :FIX Если валидно
        
//        Пробуем создать поле
        $dataInsertBatch = array();

        foreach($shipsCells as $coordinat => $status)
        {
            $dataInsertBatch[] = array('player_id' => $id, 'coordinat' => $coordinat, 'status' => $status);
        }
        
// :FIX модель Game добавляет в хранилище в обход Field
        if(!$this->DB->insertBatch('fields', $dataInsertBatch))
        {
            echo 'Fatal!!!';
        }
    }

    /**
     * Возвращает игроков
     * 
     * @return array Массив игроков
     */
    public function getPlayers()
    {
        $arrTmp = array();
        $players = $this->DB->getAll('players');

        foreach ($players as $p)
        {
            $arrTmp[] = new PlayerModel(array('playerId' => $p['id'], 'playerName' => $p['name']));
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
        $playerArr = $this->DB->getOne('players', array('id' => $playerId));

        if($playerArr)
        {
            $playerObj = new PlayerModel(array('playerId' => $playerArr['id'], 'playerName' => $playerArr['name']));
            
            return $playerObj;
        }

        return null;
    }

    /**
     * Возвращает массив в котором все поля всех игроков
     *
     * @return array Массив из полей
     */
    public function getFields()
    {
        $fieldModel = new FieldModel();
        
        $allFields = $this->DB->getAll('fields');
        $playersStates = array();
        $result = array();
        
//        разбираем ячейки игроков на отдельные массивы
        foreach ($allFields as $row)
        {
            $playersStates[$row['player_id']][$row['coordinat']] = $row['status'];
        }
        
//        формируем из этих полей столько матриц сколько игроков
        foreach ($playersStates as $playerId => $state)
        {
//            создаем пустую матрицу
            $matrix = $fieldModel->createMatrix();
//            объединяем пустую матрицу и состояния поля игрока в общую картину
            foreach ($matrix as &$row)
            {
//                перебираем строку матрицы
                foreach ($row as $coordinat => &$value)
                {
                    if (array_key_exists($coordinat, $state))
                    {
                        $value = (int)$state[$coordinat];
                    }
                }
                
                unset($value);
            }
            
            unset($row);
            
            $result[$playerId] = $matrix;
        }        
        
        return $result;
    }

    /**
     * Возвращает случайного игрока для первого хода
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