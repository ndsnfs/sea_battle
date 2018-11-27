<?php

define('BR', '<br>');

function debug(array $d)
{
    echo '<pre>';
    print_r($d);
    echo '</pre>';
}

class Validator
{
    /**
     * Разделитель координат поля x:y
     * @var string
     */
    const COORDINAT_DELIMITER = ':';

    /**
     * @var array Массив хранит найденные ошибки
     */
    private $_errors = array();

    /**
     * @var array | null временное хранилище пришедших данных
     */
    private $_TEMP = null;

    /**
     * @var array | null постоянное хранилище пришедших данных
     */
    private $_DATA = null;

    /**
     * @var array Хранит массив найденных кораблей
     */
    private $_SHIPS = array();



    /*-- Все правила --*/

    /**
     * Виды кораблей которые могут быть в игре - "кол-во палуб" => "кол-во кораблей"
     * @var array
     */
    private $_shipCntRule = array(1 => 4, 2 => 3, 3 => 2, 4 => 1);

    /**
     * Правила по которым проверяются окружающие палубу ячейки(все)
     * @var array
     */
    private $_stepRule = array('0:-1', '1:-1', '1:0', '1:1', '0:1', '-1:1', '-1:0', '-1:-1');

    /**
     * Правила по которым проверяются окружающие палубу ячейки(верх, право, низ, лево)
     * @var array
     */
    private $_commonRules = null;

    /*-- Все правила END --*/



    /**
     * @param array Массив ячеек вида x:y
     */
    public function __construct(array $data)
    {
        $this->_TEMP = $this->_DATA = array_keys($data);

        // выбираем ячейки сверху, справа, снизу, слева
        $this->_commonRules = array_filter($this->_stepRule, function($v) {
            return in_array(0, $this->_parseCoordinat($v));
        });
    }

    public function run()
    {
        // заполняет массив _SHIPS кораблями
        foreach ($this->_TEMP as $cell)
        {	
            // здесь значения из TEMP удаляются, поэтому проверяем
            // наличие ячейки в TEMP
            if(in_array($cell, $this->_TEMP))
            {
                    $this->_setShip($this->_getShip($cell));
            }
        }

        $this->_checkStep1(); // :FIX naming
        $this->_checkStep2(); // :FIX naming
    }

    /**
     * Добавляет сформированные корабли в общий массив
     * @param Array Корабль вида array('2:1', '2:2', ...)
     */
    private function _setShip(array $ship)
    {
        // Количество палуб корабля
        $shipsLength = count($ship);

        // Если есть корабль такой длины в массиве правил
        if(array_key_exists($shipsLength, $this->_shipCntRule))
        {
            // Если все корабли такой длины "вычеркнуты" из массива правил
            if($this->_shipCntRule[$shipsLength] == 0)
            {
                $this->_errors[] = 'Превышено кол-во ' . $shipsLength . '-х палубных кораблей';
            }
            else
            {
                // Уменьшаем кол-во кораблей такой длины
                $this->_shipCntRule[$shipsLength]--;
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
     * Проверка - все ли корабли расставлены
     */
    private function _checkStep1()
    {
        foreach ($this->_shipCntRule as $deckCnt => $shipsCnt)
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
            foreach ($ship as $currentCell)
            {
                //проверка оставшихся кораблей на наличие смежных ячеек
                foreach ($this->_SHIPS as $cellAll)
                {
                    // добавляем ячейке смещения
                    foreach ($this->_stepRule as $rule)
                    {
                        $checkedCell = $this->_cellAdd($currentCell, $rule);

                        if(in_array($checkedCell, $cellAll))
                        {
                            $this->_errors['ajacent'][] = 'Найдена смежная ячейка: ' . $checkedCell . ' с ' . $currentCell;
                            
                            if(!isset($this->_errors['ajacentCoordinats']))
                            {
                                $this->_errors['ajacentCoordinats'] = array();
                            }
                            
                            if(!in_array($checkedCell, $this->_errors['ajacentCoordinats']))
                            {
                                $this->_errors['ajacentCoordinats'][] = $checkedCell;
                            }
                            
                            if(!in_array($currentCell, $this->_errors['ajacentCoordinats']))
                            {
                                $this->_errors['ajacentCoordinats'][] = $currentCell;
                            }
                        }
                    }
                }
            }
        }

    }

    /**
     * Возвращает корабль вида array('2:3', '2:4', '2:5')
     * Может принимать любую координату корабля
     * @param string $cell
     */
    private function _getShip($cell)
    {
        // Удаляем при поиске крайней ячейки
        $this->_unsetFromTemp($cell);
        // проверяем окружение ячейки, т.е. состояние сверху, справа, снизу, слева
        foreach ($this->_commonRules as $coordinat)
        {
            // добавляем первой ячейке сдвиг(один шаг из четырех: вверх, вправо, вниз, влево)
            $cellEnv = $this->_cellAdd($cell, $coordinat);

            // проверяем есть ли эта ячейка в пришедших данных
            // (проверяем - это двойной корабль?)
            if(in_array($cellEnv, $this->_TEMP) )
            {
                // если есть, двигаемся в этом направлении в поиске крайней палубы(ячейки)
                $last = $this->_getLast($cellEnv, $coordinat);
                // нашли крайнюю - разворачиваемся и считаем кол-во палуб
                $createdShip = $this->_prevAll($last, $this->_ruleReverse($coordinat), array($last));
                return $createdShip;
            }
        }

        // этот ретерн возвратит корабль с одной палубой
        return array($cell);
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
        $prev = $this->_cellAdd($cell, $rule);
        // Удаляем при формировании корабля
        $this->_unsetFromTemp($cell);

        // Если палуба есть в пришедшем массиве и ее нет в уже созданных кораблях
        if(in_array($prev, $this->_DATA) && !$this->_hasCreatedShips($prev))
        {
            $tmp[] = $prev;
            return $this->_prevAll($prev, $rule, $tmp);
        }

        return $tmp;
    }

    /**
     * Находит крайнюю палубу(ячейку), т.е. сверху, справа, снизу или слева,
     * т.к. счет палуб может начаться с центра корабля
     * 
     * @param string $cell x:y
     * @param string $rule Правило сдвига
     */
    private function _getLast($cell, $rule)
    {
        // добавляем сдвиг
        $next = $this->_cellAdd($cell, $rule);
        $this->_unsetFromTemp($cell);

        if(in_array($next, $this->_TEMP))
        {
            return $this->_getLast($next, $rule);
        }

        return $cell;
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
     * Проверяет есть ли ошибки расстаноки
     * @param bool
     */
    public function isValid()
    {
        if(count($this->_errors) === 0)
        {
            return true;
        }

        return false;
    }

    /**
     * Возвращает массив ошибок
     * @return array | null
     */
    public function getErrors()
    {
        return !$this->isValid() ? $this->_errors : null;
    }



    /* вспомогательные функиции */



    /**
     * добавляет ячейке сдвиг по определенному правилу, допустим (2:5)+(0:-1) = 2:4
     */
    private function _cellAdd($cell, $rule)
    {
        $c1 = $this->_parseCoordinat($cell); // к этой ячейке приюавляем $c2
        $c2 = $this->_parseCoordinat($rule);

        $v1 = $c1[0] + $c2[0];
        $v2 = $c1[1] + $c2[1];

        return (string)$v1 . self::COORDINAT_DELIMITER . (string)$v2;
    }

    /**
     * проверяет координату на соответствие формату: "int:int"
     */
    private function _isValidCoordinat(string $c) :bool
    {
        if(preg_match("/^\-?\d:\-?\d/", $c))
        {
            return true;
        }
        return false;
    }

    /**
     * Разворачивает правило прохода по доске, например 0:1 на 0:-1(ноль не меняется)
     */
    public function _ruleReverse(string $rule)
    {
        if($this->_isValidCoordinat($rule))
        {
            $tmp = explode(':', $rule);
            $tmp = array_map(function($v) {
                    return $v * (-1);
            }, $tmp);

            return $tmp[0] . ':' . $tmp[1];
        }

        return false;
    }

    /**
     * разбивает координату на массив, например 6:6 на array(6, 6)
     */
    private function _parseCoordinat(string $c) // :array(int, int)
    {
        if($this->_isValidCoordinat($c))
        {
            $tmp = explode(':', $c);
            $tmp = array_map(function($v) {
                    return (int)$v;
            }, $tmp);

            return $tmp;
        }

        throw new Exception("Координата не валидна");

    }
}



$arr = array(
    '0:0' => 2, // 4х палубный
    '0:1' => 2, // 4х палубный
    '0:2' => 2, // 4х палубный
    '0:3' => 2, // 4х палубный
//    '0:4' => 2, // 4х палубный
    '0:5' => 2, // 4х палубный

    '2:1' => 2, // 3х палубный
    '3:1' => 2, // 3х палубный
    '4:1' => 2, // 3х палубный

    '2:3' => 2, // 3х палубный
    '3:3' => 2, // 3х палубный
    '4:3' => 2, // 3х палубный

    '2:5' => 2, // 2х палубный
    '2:6' => 2, // 2х палубный

     '0:8' => 2, // 2х палубный
     '0:9' => 2, // 2х палубный

    '3:8' => 2, // 2х палубный
    '3:9' => 2, // 2х палубный

    '9:9' => 2, // 1х палубный

    '9:6' => 2, // 1х палубный

    '9:3' => 2, // 1х палубный

    '9:1' => 2, // 1х палубный

    '1:5' => 2, // 1х палубный
);

$v = new Validator($arr);

$v->run();

if(!$v->isValid())
{
    debug($v->getErrors());
}
$err = $v->getErrors();

echo '<style>
        td {
            width: 20px;
            height: 20px;
        }
    </style>';
echo '<table>';
echo '<tr>';
	echo '<td></td>';
foreach (range(0, 9) as $v)
{
	echo '<td>'.$v.'</td>';
}
echo '</tr>';
foreach (range(0, 9) as $v)
{
	echo '<tr>';
	echo '<td>'.$v.'</td>';
	foreach (range(0, 9) as $value)
	{
		if(array_key_exists($value . ':' . $v, $arr))
		{
                    if(array_key_exists('ajacentCoordinats', $err) && in_array($value . ':' . $v, $err['ajacentCoordinats']))
                    {
			echo '<td style="background-color: red"></td>';
                    }
                    else
                    {
			echo '<td style="background-color: gray"></td>';
                    }
		}
		else
		{
			echo '<td></td>';
		}
	}
	echo '</tr>';
}
echo '</table>';