<?php

class FileDriver implements DbDriverInterface
{
	private $_path = '';

	private $_affectedRows = 0;

	private static $_instance = null;

	private function __construct()
	{
		$this->_path = ROOT_DIR . DIRECTORY_SEPARATOR . 'file_store';

		if(!file_exists($this->_path))
		{
			mkdir($this->_path);
		}
	}

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
		$filename = $this->_path . DIRECTORY_SEPARATOR . $table . '.db';

		if(!file_exists($filename))
		{
			$root = array($data);
			$f = fopen($filename, 'w');
			fwrite($f, json_encode($root));
			fclose($f);
		}
		else
		{
			$root = json_decode(file_get_contents($filename), true);

			$f = fopen($filename, 'w');
			$root[] = $data;
			fwrite($f, json_encode($root));
			fclose($f);
		}
	}


	/**
	 * имитация update
	 */
	public function update(String $table, Array $data, Array $where)
	{
		$filename = $this->_path . DIRECTORY_SEPARATOR . $table . '.db';

		if(!file_exists($filename))
		{
			throw new Exception("Таблицы не существует");
		}

		// получаем всю таблицу
		$root = json_decode(file_get_contents($filename), true);

		foreach ($root as &$row)
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
						$this->_affectedRows++;
						$row[$field] = $newValue;
					}
					else
					{
						throw new Exception("Поле не найдено");
					}
				}
			}
		}

		// если были изменения
		if($this->_affectedRows > 0)
		{
			// полностью перезаписываем файл
			$f = fopen($filename, 'w');
			fwrite($f, json_encode($root));
			fclose($f);
		}

		return true;
	}

	/**
	 * выбирает одну единственную строку
	 */
	public function getOne(String $table, Array $where)
	{
		$filename = $this->_path . DIRECTORY_SEPARATOR . $table . '.db';

		if(!file_exists($filename))
		{
			throw new Exception("Таблицы не существует");
		}

		// получаем всю таблицу
		$root = json_decode(file_get_contents($filename), true);

		foreach ($root as $row)
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
		$filename = $this->_path . DIRECTORY_SEPARATOR . $table . '.db';

		if(!file_exists($filename))
		{
			throw new Exception("Таблицы не существует");
		}

		$all = json_decode(file_get_contents($filename), true);

		return $all?: array();
	}

	/**
	 * 
	 */
	public function getWhere(String $table, Array $where)
	{
		$filename = $this->_path . DIRECTORY_SEPARATOR . $table . '.db';

		if(!file_exists($filename))
		{
			throw new Exception("Таблицы не существует");
		}

		// получаем всю таблицу
		$root = json_decode(file_get_contents($filename), true);

		$tmp = array();
		
		foreach ($root as $row)
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
		$filename = $this->_path . DIRECTORY_SEPARATOR . $table . '.db';

		if(!file_exists($filename))
		{
			throw new Exception("Таблицы не существует");
		}

		$f = fopen($filename, 'w');
		fclose($f);
	}
}