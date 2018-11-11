<?php

class Store
{
	private static $_instance = null;

	private $_players = array(); // array(array('id' => ..., 'name' => '...'), array('id' => ..., 'name' => '...'))
	private $_fields = array(); // array(array('player_id' => ..., 'field_state' => array(array('a:1' => 1, ...), ...), ...), ...)

	private function __construct(){}
	private function __clone(){}
	
	public static function getInstance()
	{
		if(self::$_instance === null)
		{
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function __get($propName)
	{
		$propName = '_' . $propName;

		if(property_exists(__CLASS__, $propName))
		{
			return $this->$propName;
		}

		return false;
	}

	// имитация insert
	public function __set($propName, $data)
	{
		$propName = '_' . $propName;
		if(property_exists(__CLASS__, $propName))
		{
			$this->$propName[] = $data;
			return true;
		}

		return false;
	}

	// имитация update
	public function update($table, Array $data, Array $where)
	{
		$propName = '_' . $table;
		if(!property_exists(__CLASS__, $propName))
		{
			throw new Exception("Таблица не найдена");
		}

		foreach ($this->$propName as &$row)
		{
			$cnt = 0;
			
			foreach ($where as $k => $v)
			{
				if($row[$k] == $v) $cnt++;
			}

			// если все условия в $where выполняются
			if($cnt === count($where))
			{
				// перебираем массив с обновлениями строки
				// и собственно обновляем эту строку
				foreach ($data as $field => $newValue)
				{
					if(array_key_exists($field, $row))
					{
						$row[$field] = $newValue;
					}
					else
					{
						throw new Exception("Поле не найдено");
					}
				}
			}
		}
	}
}