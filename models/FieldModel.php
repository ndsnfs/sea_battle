<?php

class FieldModel extends MainModel
{
    public $fieldState;
    
    public $shipsCnt;

    /**
     * Правила по которым проверяются окружающие палубу ячейки(все)
     * @var array
     */
    private static $_envRule = array('0:-1', '1:-1', '1:0', '1:1', '0:1', '-1:1', '-1:0', '-1:-1');

    /**
     * Правила по которым проверяются окружающие палубу ячейки(верх, право, низ, лево)
     * @var array
     */
    private static $_reducedEnvRules = array('0:-1', '1:0', '0:1', '-1:0');

    /**
     * @var array временное хранилище пришедшего поля
     */
    private $_TEMP;

    /**
     * @var array Хранит массив найденных кораблей
     */
    private $_SHIPS = array();
    
    public function setField(array $fieldState)
    {
        $this->fieldState = $fieldState;
        
        foreach ($fieldState as $coordinat => $state)
        {
            $createdDeck = new Deck(array('coordinat' => $coordinat, 'state' => $state));
            
//            если при создании палубы произошла ошибка останавливаем игру
            if(!$createdDeck->validate())
            {
                throw new Exception('Invalid deck');
            }
            
            $this->_TEMP[] = $createdDeck;
        }
    }
    
    public function setShipsCnt(array $shipsCnt)
    {
        $this->shipsCnt = $shipsCnt;
    }
    
    /**
     * Возвращает массив правил по которым проверяются свойства
     * 
     * @return array
     */
    public static function rules()
    {
        return array(
            'fieldState' => 'required|custom_runMainValidator'
        );
    }
    
//    метод который запускает проверку
//    связанную с расстановкой кораблей на поле
//    т.е. 
    public function runMainValidator(array $data)
    {        
//        заполняет массив _SHIPS кораблями
        foreach ($this->_TEMP as $deckObj)
        {
//            здесь значения из TEMP удаляются, поэтому проверяем
//            наличие палубы в TEMP
            if(in_array($deckObj, $this->_TEMP))
            {
//                получаем возможно корабль
                $ship = $this->_getShip($deckObj);
                $this->_setShip($ship);
            }
        }
        
        $this->_checkStep1(); // :FIX naming
        $this->_checkStep2(); // :FIX naming
        
        if($this->_errors)
        {
            debug($this->_errors);
        }
    }
    
    /**
     * Проверка - все ли корабли расставлены
     */
    private function _checkStep1()
    {
        foreach ($this->shipsCnt as $deckCnt => $shipsCnt)
        {
            if($shipsCnt !== 0)
            {
                $this->_errors[$deckCnt] = 'Не хватает ' . $shipsCnt . ' корабля с ' . $deckCnt . ' палубами';
            }
        }
    }

    /**
     * Проверяет корабли на наличие близлежащих(смежных)
     * @return void
     */
    private function _checkStep2()
    {
        while($ship = array_shift($this->_SHIPS))
        {
            // перебираем выбранный корабль
            foreach ($ship as $currentDeck)
            {
                //проверка оставшихся кораблей на наличие смежных ячеек
                foreach ($this->_SHIPS as $cellAll)
                {
                    // добавляем ячейке смещения
                    foreach (self::$_envRule as $rule)
                    {
                        $checkedCell = $currentDeck->addOffset($rule);

                        if(in_array($checkedCell, $cellAll))
                        {
                            $this->_errors['ajacent'][] = 'Найдена смежная ячейка: ' . $checkedCell->coordinat . ' с ' . $currentDeck->coordinat;
                            
                            if(!isset($this->_errors['ajacentCoordinats']))
                            {
                                $this->_errors['ajacentCoordinats'] = array();
                            }
                            
                            if(!in_array($checkedCell, $this->_errors['ajacentCoordinats']))
                            {
                                $this->_errors['ajacentCoordinats'][] = $checkedCell->coordinat;
                            }
                            
                            if(!in_array($currentDeck->coordinat, $this->_errors['ajacentCoordinats']))
                            {
                                $this->_errors['ajacentCoordinats'][] = $currentDeck->coordinat;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Добавляет сформированные корабли в общий массив
     * @param Array Корабль вида array('2:1', '2:2', ...)
     */
    private function _setShip($ship)
    {
        // Количество палуб корабля
        $shipsLength = count($ship);

        // Если есть корабль такой длины в массиве правил
        if(array_key_exists($shipsLength, $this->shipsCnt))
        {
            // Если все корабли такой длины "вычеркнуты" из массива правил
            if($this->shipsCnt[$shipsLength] == 0)
            {
                $this->_errors[] = 'Превышено кол-во ' . $shipsLength . '-х палубных кораблей';
            }
            else
            {
                // Уменьшаем кол-во кораблей такой длины
               $this->shipsCnt[$shipsLength]--;
            }
        }
        else
        {
            $this->_errors[] = 'Корабля с ' . $shipsLength . ' палубами быть не может';
        }

        // корабль добавляем в любом случае
        $this->_SHIPS[] = $ship;
    }

    /**
     * Возвращает минимум один корабль
     * @param string $cell
     */
    private function _getShip($deckObj)
    {
        // Удаляем при поиске крайней ячейки
        $this->_unsetFromTemp($deckObj);
        // проверяем окружение ячейки, т.е. состояние сверху, справа, снизу, слева
        foreach (self::$_reducedEnvRules as $rule)
        {
            // добавляем первой ячейке сдвиг(один шаг из четырех: вверх, вправо, вниз, влево)
            $checkedCell = $deckObj->addOffset($rule);

            // проверяем есть ли эта ячейка в пришедших данных
            // (проверяем - это двойной корабль?)
            echo '<pre>';
            var_dump($checkedCell);
//            var_dump($this->_TEMP);
            echo '</pre>';
            if(in_array($checkedCell, $this->_TEMP))
            {
                // если есть, двигаемся в этом направлении в поиске крайней палубы(ячейки)
                $last = $this->_getLast($checkedCell, $rule);
                // нашли крайнюю - разворачиваемся и считаем кол-во палуб
                $createdShip = $this->_prevAll($last, $this->_ruleReverse($rule), array($last));
                return $createdShip;
            }
        }

        // этот ретерн возвратит корабль с одной палубой
        return array($deckObj);
    }

    /**
     * Находит крайнюю палубу(ячейку), т.е. сверху, справа, снизу или слева,
     * т.к. счет палуб может начаться с центра корабля
     * 
     * @param object $deckObj 
     * @param string $rule Правило сдвига
     */
    private function _getLast($deckObj, $rule)
    {
        // добавляем сдвиг
        $next = $deckObj->addOffset($rule);
        $this->_unsetFromTemp($deckObj);

        if(in_array($next, $this->_TEMP))
        {
            return $this->_getLast($next, $rule);
        }

        return $deckObj;
    }

    /**
     * Создает рекурсивно набор ячеек от самой крайней найденной методом _getLast()
     *
     * @param string $cell
     * @param string $rule Правило сдвига
     * @param array $tmp Формируемый корабль
     * @return array Сформированный корабль
     */
    private function _prevAll($cell, $rule, $tmp)
    {
        // добавляем сдвиг
        $prev = $cell->addOffset($rule);
        // Удаляем при формировании корабля
        $this->_unsetFromTemp($cell);
        // Если палуба есть в пришедшем массиве и ее нет в уже созданных кораблях
        if(in_array($prev->coordinat, $this->fieldState) && !$this->_hasCreatedShips($prev))
        {
            $tmp[] = $prev;
            return $this->_prevAll($prev, $rule, $tmp);
        }

        return $tmp;
    }

    /**
     * удаляет ячейку, хранящуюся во временном массиве
     * @param string $cell x:y
     * @return bool
     */
    private function _unsetFromTemp($cell)
    {
        $k = array_search($cell, $this->_TEMP);

        if($k !== false)
        {
            unset($this->_TEMP[$k]);
            return true;
        }

        return false;
    }
    
    /**
     * Проверяет - содержат ли созданные корабли приходящую ячейку
     * @param string $cell
     * @return bool
     */
    public function _hasCreatedShips($cell)
    {
        foreach($this->_SHIPS  as $ship)
        {
            if(in_array($cell, $ship))
            {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Разворачивает правило прохода по доске, например 0:1 на 0:-1(ноль не меняется)
     */
    public function _ruleReverse(string $rule)
    {
        $tmp = explode(':', $rule);
        $tmp = array_map(function($v) {
                return $v * (-1);
        }, $tmp);

        return $tmp[0] . ':' . $tmp[1];

        return false;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    const EMPTY_CELL = 1;
    const SHIP_CELL = 2;
    const FAILED_CELL = 3;
    const WOUND_CELL = 4;

    const MIN_COORDINAT = 0;
    const MAX_COORDINAT = 9;
    

    /**
     * возвращает максимальное значение координаты(x или y)
     */
    public static function getMaxCoordinat()
    {
        return self::MAX_COORDINAT;
    }

    /**
     * возвращает минимальное значение координаты(x или y)
     */
    public static function getMinCoordinat()
    {
        return self::MIN_COORDINAT;
    }

    /**
     * пустая ячейка
     */
    public static function getEmptyCell()
    {
        return self::EMPTY_CELL;
    }

    /**
     * корабль ячейка
     */
    public static function getShipCell()
    {
        return self::SHIP_CELL;
    }

    /**
     * промах ячейка
     */
    public static function getFailedCell()
    {
        return self::FAILED_CELL;
    }

    /**
     * подбитая ячейка
     */
    public static function getWoundCell()
    {
        return self::WOUND_CELL;
    }
    
    /**
     * Создает матрицу x:y
     * @return array
     */
    public function createMatrix()
    {
        $matrix = array();
        
        foreach (self::getEnum() as $y)
        {
            foreach (self::getEnum() as $x => $yValue)
            {
               $matrix[$y][$x . ':' . $y] = self::EMPTY_CELL;
            }
        }
        
        return $matrix;
    }

    /**
     * Просто создает перечисление
     * @return array
     */
    public static function getEnum()
    {
        return range(self::MIN_COORDINAT, self::MAX_COORDINAT);
    }

    public function getState()
    {
        return $this->_state;
    }
}