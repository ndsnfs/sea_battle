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

	public function __construct()
	{
		$this->_state = $this->getEmptyState();
	}

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
	 * создает матрицу
	 */
	public function getEmptyState()
	{
		/**
		 * при создании объекта инициализируется пустое поле вида
		 * array(10){
		 *		array(10) {
		 * 			[0:0"]=> int(1)
		 * 			[0:1"]=> int(1)
		 * 			[0:2"]=> int(1)
		 * 			[0:3"]=> int(1)
		 * 			[0:4"]=> int(1)
		 * 			[0:5"]=> int(1)
		 * 			[0:6"]=> int(1)
		 * 			[0:7"]=> int(1)
		 * 			[0:8"]=> int(1)
		 * 			[0:9"]=> int(1)
		 *		},
		 *		....
		 * }
		 */

		$tmpArr = array();

		foreach (self::getEnum() as $ky => $xValue)
		{
			foreach (self::getEnum() as $kx => $yValue)
			{
				$tmpArr[$ky][$yValue . ':' . $xValue] = self::EMPTY_CELL;
			}
		}

		return $tmpArr;
	}

	public static function getEnum()
	{
		return range(self::MIN_COORDINAT, self::MAX_COORDINAT);
	}

	public function getState()
	{
		return $this->_state;
	}

	public function changeState(Array $newState)
	{
		// формат newState: array('a:1' => 2, 'b:4' => 2)
		$newStateKeys = array_keys($newState);

		foreach ($this->_state as &$row)
		{
			foreach ($newStateKeys as $k)
			{
				if(array_key_exists($k, $row))
				{
					$row[$k] = (int)$newState[$k];
				}
			}
		}
	}
}