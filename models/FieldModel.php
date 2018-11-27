<?php

class FieldModel
{	
    const EMPTY_CELL = 1;
    const SHIP_CELL = 2;
    const FAILED_CELL = 3;
    const WOUND_CELL = 4;

    const MIN_COORDINAT = 0;
    const MAX_COORDINAT = 9;

    private $_state = array();

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