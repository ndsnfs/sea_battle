<?php

class QueryCreator
{
    const SELECT_TYPE = 1;
    const UPDATE_TYPE = 2;
    const INSERT_TYPE = 3;
    const DELETE_TYPE = 4;
    
    private $_sql;
    private $_select;
    private $_insert;
    private $_delete;
    private $_join = '';
    private $_where;
    private $_update;
    private $_limit;
    
    private $_type;
    
    private $_values = [];
    
    public function setInsert(string $table, $set)
    {
        $this->_type = self::INSERT_TYPE;
        $coolStr = implode(', ', array_keys($set));
        $ques = implode(', ', array_fill(0, count($set), '?'));
        $this->_values = array_values($set);
        
        $this->_insert = 'INSERT INTO ' . $table . ' (' . $coolStr . ') VALUES ( ' . $ques . ')';
    }
    
    public function setSelect(string $table, array $cols = NULL)
    {
        $this->_type = self::SELECT_TYPE;
        
        if(is_array($cols) && count($cols) > 0)
        {
            $c = implode(', ', $cols);
        }
        else
        {
            $c = '*';
        }
        
        $this->_select = 'SELECT ' . $c . ' FROM ' . $table;
    }
    
    public function setDelete(string $table)
    {
        $this->_type = self::DELETE_TYPE;
        $this->_delete = 'DELETE FROM ' . $table;
    }
    
    public function setJoin(array $join)
    {
        foreach ($join as $j)
        {
            if(array_key_exists('table', $j)
                    && array_key_exists('on', $j)
                    && array_key_exists('type', $j)
                    && !empty($j['table'])
                    && !empty($j['on'])
                    && !empty($j['type']))
            {
                $this->_join .= ' ' . $j['type'] . ' JOIN ' . $j['table'] . ' ON ' . $j['on'];
            }
        }
    }
    
    public function setWhere(array $where)
    {
        $cols = array_keys($where);
        
        if(count($cols) > 0)
        {
            $first = array_shift($cols);
            $this->_where .= ' WHERE ' . $first . ' = ?';
            $this->_values[] = $where[$first];
        
            foreach ($cols as $col)
            {
                $this->_where .= ' AND ' . $col . ' = ?';
                $this->_values[] = $where[$col];
            }
        }
    }
    
    public function setUpdate(string $table, array $set)
    {
        $this->_type = self::UPDATE_TYPE;
        $setterCools = array_keys($set);
        $this->_update = 'UPDATE ' . $table . ' SET';
        
        foreach ($setterCools as $col)
        {
            if(end($setterCools) === $col)
            {
                $this->_update .= ' ' . $col . ' = ?';
            }
            else
            {
                $this->_update .= ' ' . $col . ' = ?,';
            }
            
            $this->_values[] = $set[$col];
        }
    }
    
    public function setLimit(int $i)
    {
        $this->_limit = ' LIMIT ' . $i;
    }
    
    public function create()
    {        
        switch ($this->_type)
        {
            case self::SELECT_TYPE: $sql = $this->_select . $this->_join . $this->_where . $this->_limit; break;
            case self::DELETE_TYPE: $sql = $this->_delete . $this->_where . $this->_limit; break;
            case self::INSERT_TYPE: $sql = $this->_insert; break;
            case self::UPDATE_TYPE: $sql = $this->_update . $this->_where; break;
            default : $sql = FALSE;
        }
        
        return $sql;
    }
    
    public function getValues()
    {
        return $this->_values;
    }
}

