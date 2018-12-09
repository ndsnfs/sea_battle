<?php

class DB implements DbDriverInterface
{
    private $_driver;

    public function __construct()
    {
//        :FIX организовать нормальное подключение конфига
        $this->_driver = call_user_func('PgsqlDriver::getInstance');
    }
    
    public function join(string $tb, string $on, string $type = 'INNER')
    {
        $this->_driver->join($tb, $on, $type);
    }
    
    public function lastQuery()
    {
        return $this->_driver->lastQuery();
    }
    
    /**
     * Запускает транзакцию
     */
    public function transBegin()
    {
        $this->_driver->transBegin();
    }
    
    /**
     * Фиксирует транзакцию
     */
    public function transCommit()
    {
        $this->_driver->transCommit();
    }
    
    /**
     * Откат изменений
     */
    public function transRollback()
    {
        $this->_driver->transRollback(); 
    }

    public function insert(string $table, array $data)
    {
        return $this->_driver->insert($table, $data);
    }

    public function insertBatch(string $table, array $data)
    {
        return $this->_driver->insertBatch($table, $data);
    }

    public function update(string $table, array $data, array $where = array())
    {
        return $this->_driver->update($table, $data, $where);
    }

    public function getOne(string $table, array $where, array $cols = [])
    {
        return $this->_driver->getOne($table, $where, $cols);
    }

    public function getAll(string $table, array $cols = [])
    {
        return $this->_driver->getAll($table, $cols);
    }

    public function getWhere(string $table, array $where, array $cols = [])
    {
        return $this->_driver->getWhere($table, $where, $cols);
    }

    public function clear(string $table)
    {
        return $this->_driver->clear($table);
    }

    public function delete(string $table, array $where)
    {
        $this->_driver->delete($table, $where);
    }
    public function replace(string $table, array $where, array $dataRow)
    {
        $this->_driver->replace($table, $where, $dataRow);
    }
}