<?php

class DB
{
	public function getAll($table)
	{
		$store = Store::getInstance();
		return $store->$table;
	}

	public function getWhere($table, Array $where)
	{
		$store = Store::getInstance();

		$tmp = array();
		
		foreach ($store->$table as $row)
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

	public function insert($table, Array $data)
	{
		$store = Store::getInstance();
		return $store->$table = $data;
	}

	public function update($table, Array $data, Array $where)
	{
		// $store = Store::getInstance();

		// $tmp = array();
		
		// foreach ($store->$table as $row)
		// {
		// 	$cnt = 0;
			
		// 	foreach ($where as $k => $v)
		// 	{
		// 		if($row[$k] == $v) $cnt++;
		// 	}

		// 	if($cnt === count($where)) $tmp[] = $row;
		// }

		// return $tmp;
	}
}