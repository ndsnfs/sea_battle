<?php

class DB implements DbDriverInterface
{
	private $_driver;

	public function __construct()
	{
		global $conf;
		$driverDb = $conf['db']['driver'];
		$this->_driver = call_user_func($driverDb . '::getInstance');
	}

	public function insert(String $table, Array $data)
	{
		return $this->_driver->insert($table, $data);
	}

	public function update(string $table, Array $data, Array $where = array())
	{
		return $this->_driver->update($table, $data, $where);
	}

	public function getOne(string $table, Array $where)
	{
		return $this->_driver->getOne($table, $where);
	}

	public function getAll(string $table)
	{
		return $this->_driver->getAll($table);
	}

	public function getWhere(string $table, Array $where)
	{
		return $this->_driver->getWhere($table, $where);
	}
}