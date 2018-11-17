<?php

/**
 *  класс Store имитирует хранилище
 */
class Store implements DbDriverInterface
{
	private static $_instance = null;

	/**
	 * содержит id, name
	 */
	private $_players = array(); // array(array('id' => ..., 'name' => '...'), array('id' => ..., 'name' => '...'))

	/**
	 * содержит поля player_id, field_state(нарушена первая нормальная форма)
	 */
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

	/**
	 * имитация insert
	 * @table string
	 * @data array
	 */
	public function insert(String $table, Array $data)
	{
		$tableName = '_' . $table;

		if(property_exists(__CLASS__, $tableName))
		{
			$this->$tableName[] = $data;
			return true;
		}

		return false;
	}

	/**
	 * имитация update
	 */
	public function update(String $table, Array $data, Array $where)
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

	/**
	 * выбирает одну единственную строку
	 */
	public function getOne(String $table, Array $where)
	{
		$tableName = '_' . $table;

		if(!property_exists(__CLASS__, $tableName))
		{
			throw new Exception("Таблица не найдена");
		} 

		foreach ($this->$tableName as $row)
		{
			$cnt = 0;
			
			foreach ($where as $k => $v)
			{
				if($row[$k] == $v) $cnt++;
			}

			if($cnt === count($where)) return $row;
		}

		return false;
	}

	/**
	 * 
	 */
	public function getAll(String $table)
	{
		$tableName = '_' . $table;

		if(!property_exists(__CLASS__, $tableName))
		{
			throw new Exception("Таблица не найдена");
		}

		return $this->$tableName;
	}

	/**
	 * 
	 */
	public function getWhere(String $table, Array $where)
	{
		$tableName = '_' . $table;

		if(!property_exists(__CLASS__, $tableName))
		{
			throw new Exception("Таблица не найдена");
		}

		$tmp = array();
		
		foreach ($this->$tableName as $row)
		{
			$cnt = 0;
			
			foreach ($where as $k => $v)
			{
				if($row[$k] == $v) $cnt++;
			}

			if($cnt === count($where)) $tmp[] = $row;
		}

		return $tmp;
	}

	public function clear(String $table)
	{
		$tableName = '_' . $table;

		if(!property_exists(__CLASS__, $tableName))
		{
			throw new Exception("Таблица не найдена");
		}

		$this->$tableName = array();
	}
}