<?php

// var_dump(json_encode(array('d' => 1, 'e' => 3, 'f' => array(1,2,3,4))));
// exit;
define('BR', '<br>');

function debug(array $d)
{
	echo '<pre>';
	print_r($d);
	echo '</pre>';
}

class Validator
{
	const FIELD_SIZE = 10;
	const MAX_SHIP_SIZE = 4;
	const COORDINAT_DELIMITER = ':';

	/**
	 * временное хранилище пришедших данных
	 */
	private $_tmpData = null;

	/**
	 * постоянное хранилище пришедших данных
	 */
	private $_data = null;

	private $_rules = array('0:-1', '1:-1', '1:0', '1:1', '0:1', '-1:1', '-1:0', '-1:-1');
	private $_commonRules = null;

	public function __construct(array $data)
	{
		$this->_tmpData = $this->_data = array_keys($data);

		// выбираем ячейки сверху, справа, снизу, слева
		$this->_commonRules = array_filter($this->_rules, function($v) {
			return in_array(0, $this->_parseCoordinat($v));
		});
	}

	public function isValid()
	{
		foreach ($this->_tmpData as $c)
		{
			if(in_array($c, $this->_tmpData))
			{
				echo 'Получена первая ячейка' . $c . BR;
				$this->_getDeckCnt($c);
			}
		}
	}

	private function _getDeckCnt($c)
	{
		// проверяем окружение ячейки, т.е. состояние сверху, справа, снизу, слева
		foreach ($this->_commonRules as $coordinat)
		{	
			// добавляем ячейке сдвиг(один шаг из четырех: вверх, вправо, вниз, влево)
			$next = $this->_cellAdd($c, $coordinat);

			// проверяем есть ли эта ячейка в пришедших данных
			// (проверяем - это двойной корабль?)
			if(in_array($next, $this->_tmpData))
			{
				echo 'Найдено окружение. Координата:' . $next . BR;
				$k = array_search($c, $this->_tmpData);
				echo 'Удаляем ' . $this->_tmpData[$k] . BR;
				unset($this->_tmpData[$k]);
				// если есть, двигаемся в этом направлении в поиске след. ячеек
				echo 'Всего ' . $this->_next($next, $coordinat, 2) . BR;
			}
		}
	}

	private function _next($cell, $rule, $cnt)
	{
		$next = $this->_cellAdd($cell, $rule);

		if(in_array($next, $this->_tmpData))
		{
			echo 'Следующая найдена. Координата:' . $next . BR;
			$k = array_search($cell, $this->_tmpData);
			echo 'Удаляем ' . $this->_tmpData[$k] . BR;
			unset($this->_tmpData[$k]);
			$cnt++;
			return $this->_next($next, $rule, $cnt);
		}


		$k = array_search($cell, $this->_tmpData);
		echo 'Удаляем ' . $this->_tmpData[$k] . BR;
		unset($this->_tmpData[$k]);

		return $cnt;
	}




	private function _cellAdd($cellOne, $cellThwo)
	{
		$c1 = $this->_parseCoordinat($cellOne); // к этой ячейке приюавляем $c2
		$c2 = $this->_parseCoordinat($cellThwo);

		$v1 = $c1[0] + $c2[0];
		$v2 = $c1[1] + $c2[1];

		return (string)$v1 . self::COORDINAT_DELIMITER . (string)$v2;
	}

	private function _isValidCoordinat(string $c) :bool
	{
		if(preg_match("/^\-?\d:\-?\d/", $c))
		{
			return true;
		}
		return false;
	}

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

		return false;
	}
}

$v = new Validator(array('2:2' => 2, '2:3' => 2, '2:4' => 2, '2:5' => 2, '6:6' => 2, '6:7' => 2, '6:8' => 2, '8:1' => 2));


$v->isValid();