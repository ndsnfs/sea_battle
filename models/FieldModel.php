<?php

class FieldModel
{	
	const EMPTY_CELL = 1;
	const SHIP_CELL = 2;
	const FAILED_CELL = 3;
	const WOUND_CELL = 3;

	private $_state = array();

	public function __construct()
	{
		$this->_state = $this->getEmptyState();
	}

	public function getEmptyState()
	{
		/**
		 * при создании объекта инициализируется пустое поле вида
		 * array(10){
		 *		array(10) {
		 * 			[a:1"]=> int(1)
		 * 			[a:2"]=> int(1)
		 * 			[a:3"]=> int(1)
		 * 			[a:4"]=> int(1)
		 * 			[a:5"]=> int(1)
		 * 			[a:6"]=> int(1)
		 * 			[a:7"]=> int(1)
		 * 			[a:8"]=> int(1)
		 * 			[a:9"]=> int(1)
		 * 			[a:10"]=> int(1)
		 *		},
		 *		....
		 * }
		 */

		$tmpArr = array();

		foreach (self::getYEnum() as $ky => $xValue)
		{
			foreach (self::getXEnum() as $kx => $yValue)
			{
				$tmpArr[$ky][$yValue . ':' . $xValue] = self::EMPTY_CELL;
			}
		}

		return $tmpArr;
	}

	public static function getXEnum()
	{
		return range('a', 'j');
	}

	public static function getYEnum()
	{
		return range(1, 10);
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