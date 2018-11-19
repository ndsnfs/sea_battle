<?php

function debug(array $d)
{
	echo '<pre>';
	print_r($d);
	echo '</pre>';
}

class Validator
{
	const COORDINAT_DELIMITER = ':';

	/**
	 * временное хранилище пришедших данных
	 */
	private $_TEMP = null;

	/**
	 * постоянное хранилище пришедших данных
	 */
	private $_DATA = null;

	private $_SHIPS = array();

	/**
	 * количество разнопалубных кораблей - array("кол-во палуб" => "кол-во кораблей")
	 */
	private $_shipCntRule = array(1 => 4, 2 => 3, 3 => 2, 4 => 1);

	/**
	 * правила по которым проверяются окружающие палубу ячейки(все)
	 */
	private $_stepRule = array('0:-1', '1:-1', '1:0', '1:1', '0:1', '-1:1', '-1:0', '-1:-1');

	/**
	 * правила по которым проверяются окружающие палубу ячейки(верх, право, низ, лево)
	 */
	private $_commonRules = null;

	public function __construct(array $data)
	{
		$this->_TEMP = $this->_DATA = array_keys($data);

		// выбираем ячейки сверху, справа, снизу, слева
		$this->_commonRules = array_filter($this->_stepRule, function($v) {
			return in_array(0, $this->_parseCoordinat($v));
		});
	}

	private function _setShip(Array $ship)
	{
		$cnt = count($ship);

		if(array_key_exists($cnt, $this->_shipCntRule) && $this->_shipCntRule[$cnt] !== 0)
		{
			$this->_shipCntRule[$cnt]--;
			$this->_SHIPS[] = $ship;
		}
		else
		{
			throw new Exception("Шото с количеством палуб намудрено");
		}
	}

	public function isValid()
	{
		foreach ($this->_TEMP as $c)
		{
			if(in_array($c, $this->_TEMP))
			{
				$this->_setShip($this->_getShip($c)); // получает последнюю палубу
			}
		}

		debug($this->_SHIPS);
	}

	/**
	 * возвращает корабль вида array('2:3', '2:4', '2:5')
	 */
	private function _getShip($c)
	{
		// проверяем окружение ячейки, т.е. состояние сверху, справа, снизу, слева
		foreach ($this->_commonRules as $coordinat)
		{
			// добавляем ячейке сдвиг(один шаг из четырех: вверх, вправо, вниз, влево)
			$next = $this->_cellAdd($c, $coordinat);
			$this->_unsetFromTemp($c);

			// проверяем есть ли эта ячейка в пришедших данных
			// (проверяем - это двойной корабль?)
			if(in_array($next, $this->_TEMP))
			{
				// если есть, двигаемся в этом направлении в поиске крайней палубы(ячейки)
				$last = $this->_getLast($next, $coordinat);
				// нашли крайнюю - разворачиваемся и считаем кол-во палуб
				return $this->_prevAll($last, $this->_ruleReverse($coordinat), array($last));
			}
		}

		// этот ретерн возвратит корабль с одной палубой
		return array($c);
	}

	/**
	 * создает набор ячеек от самой крайней найденной методом _getLast()
	 */
	private function _prevAll($cell, $rule, $tmp)
	{
		// добавляем сдвиг
		$prev = $this->_cellAdd($cell, $rule);
		$this->_unsetFromTemp($cell);

		if(in_array($prev, $this->_DATA))
		{
			$tmp[] = $prev;

			return $this->_prevAll($prev, $rule, $tmp);
		}

		return $tmp;
	}

	/**
	 * находит крайнюю палубу(ячейку), т.е. сверху, справа, снизу или слева
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
	 * разворачивает правило прохода по доске, например 0:1 на 0:-1(ноль не меняется)
	 */
	public function _ruleReverse(String $rule)
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





$v = new Validator(array('2:5' => 2, '2:3' => 2, '2:2' => 2, '2:4' => 2, '2:6' => 2, '6:6' => 2, '6:7' => 2, '6:8' => 2, '8:1' => 2, '7:1' => 2,'9:1' => 2, '8:2' => 2));


$v->isValid();