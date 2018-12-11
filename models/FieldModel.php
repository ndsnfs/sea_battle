<?php

class FieldModel extends MainModel
{
    /*-- ERRORS --*/
   
    public $_selfErrors = [];
    
    /**
     * Смежные координаты
     * @var array 
     */
    public $ajacentCoordinats = [];
    
    /*-- ERRORS END --*/
    
    
    
    /*-- STATE --*/
    
    public $fieldState;
    
    /**
     * Хранит в себе созданное плоское поле - из объектов Cell
     * @var array
     */
    public $FIELD;
    
    /**
     * Хранит координаты палуб(не кораблей!) в плоском виде
     * @var array 
     */
    public $SHIPS_COORDINAT;

    /**
     * @var array Временное хранилище для палуб
     */
    private $_TEMP = array();

    /**
     * @var array Хранит массив найденных кораблей при парсинге
     */
    private $_SHIPS = array();
    
    /*-- STATE END --*/
    
    
    
    /*-- RULES --*/
    
    private $_minCoordinat = 0;
    
    /**
     * Максимальный размер поля
     * @var int 
     */
    public $maxCoordinat;
    
    /**
     * Содержит кол-во кораблей с кол-вом палуб
     * @var array 
     */
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
     * Возвращает массив правил по которым проверяются свойства
     * @return array
     */
    public static function rules()
    {
        return array(
            'fieldState' => 'custom_mainValidator',
            'maxCoordinat' => 'required|int'
        );
    }
    
    /*-- RULES END --*/
    
    
    
    /*-- SETTERS --*/
    
    /**
     * Задает  максимальный размер поля
     * @param int $var
     */
    public function setMaxCoordinat($var)
    {
        $this->maxCoordinat = $var;
    }
    
    /**
     * Задает кол-во кораблей с опред-ым кол-вом палуб
     * @param array $shipsCnt
     */
    public function setShipsCnt(array $shipsCnt)
    {
        $this->shipsCnt = $shipsCnt;
    }
    
    /**
     * Создает плоский массив. Вместо метода load
     * @param mixed  $fieldState Массив, где ключами являются координаты,
     * а значения состояния этих координат
     */
    public function createField($fieldState = [])
    {
//        :FIX mixed - это плохо
        if(!is_array($fieldState))
        {
            $fieldState = [];
        }
        
//     Пздц как не нравится, но пока так   
        $this->fieldState = $fieldState;
        
//        на старте x и y одинаковые
        $x = $y = $this->_minCoordinat;
        
        while(true)
        {
            $coordinat = $x . ':' . $y;
            $state = Cell::getEmptyCell();
            
            if(array_key_exists($coordinat, $fieldState))
            {
                $state = $fieldState[$coordinat];
            }
            
            $cell = new Cell(array('coordinat' => $coordinat, 'state' => $state));
            
            if(!$cell->validate())
            {
//                т.к. поле должно быть создано для показа ошибок пользователю
//                создаем пустую ячейку
                $cell = new Cell(array('coordinat' => $coordinat, 'state' => Cell::getEmptyCell()));
                
            }
            
            $this->FIELD[] = $cell;
            
            $x++;
            
            if($x > $this->maxCoordinat)
            {
                $x = $this->_minCoordinat;
                $y++;
            }
//            если состояние координаты  - корабль, тогда формируем времменные хранилища
            if((int)$state == Cell::getShipCell())
            {
                $this->SHIPS_COORDINAT[] = $coordinat;
                $this->_TEMP[] = $cell;
            }
            
//            если по $y превышено макс число - поле создано
            if($y > $this->maxCoordinat)
            {
                break;
            }
        }
    }
    
    /*-- SETTERS END --*/
    
    
    
    /*-- GETTERS --*/
    
    /**
     * пустая ячейка
     */
    public static function getEmptyCell()
    {
        return Cell::getEmptyCell();
    }

    /**
     * корабль ячейка
     */
    public static function getShipCell()
    {
        return Cell::getShipCell();
    }

    /**
     * промах ячейка
     */
    public static function getFailedCell()
    {
        return Cell::getFailedCell();
    }

    /**
     * подбитая ячейка
     */
    public static function getWoundCell()
    {
        return Cell::getWoundCell();
    }
    
    /**
     * Массив смежных координат(строк)
     * @return array
     */
    public function getAjacentCoordinats()
    {
        return array('ajacentCoordinats' => $this->ajacentCoordinats);
    }
    
    /*-- GETTERS END --*/
    
    
    
    /*-- VALIDATION --*/
    
    /**
     * Кастомный метод валидации который запускает проверку
     * связанную с расстановкой кораблей на поле
     * 
     * @return boolean
     */
    public function mainValidator()
    {        
//        заполняет массив _SHIPS кораблями
        foreach ($this->_TEMP as $deckObj)
        {
//            здесь значения из TEMP удаляются, поэтому проверяем
//            наличие палубы в TEMP
            if(in_array($deckObj, $this->_TEMP))
            {
//                получаем и сохраняем возможно корабль                
                $this->_setShip($this->_getShip($deckObj));
            }
        }
        
        $this->_checkStep1(); // :FIX naming
        $this->_checkStep2(); // :FIX naming
        
        if(count($this->_selfErrors) > 0)
        {
            $this->customErrors['mainValidator'] = $this->_selfErrors;
            return false;
        }
        
        return true;
    }
    
    /**
     * Проверка - все ли корабли расставлены
     * @return void
     */
    private function _checkStep1()
    {
        foreach ($this->shipsCnt as $deckCnt => $shipsCnt)
        {
            if($shipsCnt !== 0)
            {
                $this->_selfErrors[] = 'Не хватает ' . $shipsCnt . ' корабля с ' . $deckCnt . ' палубами';
            }
        }
    }

    /**
     * Проверяет корабли на наличие близлежащих(смежных)
     * @return void
     */
    private function _checkStep2()
    {
        while($checkedShip = array_shift($this->_SHIPS))
        {
            // перебираем выбранный корабль
            foreach ($checkedShip as $checkedDeck)
            {
                //проверка оставшихся кораблей на наличие смежных ячеек
                foreach ($this->_SHIPS as $otherShip)
                {
                    // добавляем ячейке смещения
                    foreach (self::$_envRule as $rule)
                    {
                        $checkedCell = $checkedDeck->addOffset($rule);

                        if(in_array($checkedCell, $otherShip))
                        {
                            $this->_selfErrors[] = 'Найдена смежная ячейка: ' . $checkedCell->coordinat . ' с ' . $checkedDeck->coordinat;
                            
                            if(!in_array($checkedCell->coordinat, $this->ajacentCoordinats))
                            {
                                $this->ajacentCoordinats[] = $checkedCell->coordinat;
                            }
                            
                            if(!in_array($checkedDeck->coordinat, $this->ajacentCoordinats))
                            {
                                $this->ajacentCoordinats[] = $checkedDeck->coordinat;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Добавляет сформированные корабли в общий массив
     * @param array $ship Массив объектов
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
                $this->_selfErrors[] = 'Превышено кол-во ' . $shipsLength . '-х палубных кораблей';
            }
            else
            {
                // Уменьшаем кол-во кораблей такой длины
               $this->shipsCnt[$shipsLength]--;
            }
        }
        else
        {
            $this->_selfErrors[] = 'Корабля с ' . $shipsLength . ' палубами быть не может';
        }

        // корабль добавляем в любом случае
        $this->_SHIPS[] = $ship;
    }

    /**
     * Возвращает минимум однопалубный корабль
     * @param object $cell
     */
    private function _getShip($cell)
    {
        // Удаляем при поиске крайней ячейки
        $this->_unsetFromTemp($cell);
        // проверяем окружение ячейки, т.е. состояние сверху, справа, снизу, слева
        foreach (self::$_reducedEnvRules as $rule)
        {
            // добавляем первой ячейке сдвиг(один шаг из четырех: вверх, вправо, вниз, влево)
            $checkedCell = $cell->addOffset($rule);

            // проверяем есть ли эта ячейка в пришедших данных
            // (проверяем - это двойной корабль?)
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
        return array($cell);
    }

    /**
     * Находит крайнюю палубу(ячейку), т.е. сверху, справа, снизу или слева,
     * т.к. счет палуб может начаться с центра корабля
     * 
     * @param object $cell 
     * @param string $rule Правило сдвига
     */
    private function _getLast($cell, $rule)
    {
        // добавляем сдвиг
        $next = $cell->addOffset($rule);
        $this->_unsetFromTemp($cell);

        if(in_array($next, $this->_TEMP))
        {
            return $this->_getLast($next, $rule);
        }

        return $cell;
    }

    /**
     * Создает рекурсивно набор ячеек от самой крайней найденной методом _getLast()
     *
     * @param object $cell
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
        if(in_array($prev->coordinat, $this->SHIPS_COORDINAT) && !$this->_hasCreatedShips($prev))
        {
            $tmp[] = $prev;
            return $this->_prevAll($prev, $rule, $tmp);
        }

        return $tmp;
    }

    /**
     * Удаляет ячейку, хранящуюся во временном массиве
     * @param object $cell x:y
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
     * @param object $cell
     * @return bool
     */
    public function _hasCreatedShips($cell)
    {        
        foreach($this->_SHIPS as $ship)
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
     * @param string $rule координата вида x:y
     */
    public function _ruleReverse(string $rule)
    {
        $tmp = explode(':', $rule);
        $tmp = array_map(function($v) {
                return $v * (-1);
        }, $tmp);

        return $tmp[0] . ':' . $tmp[1];
    }
    
    /*-- VALIDATION END --*/
}