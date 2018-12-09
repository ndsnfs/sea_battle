<?php

class QueryCreator
{
    private $_sql;
    private $_select;
    private $_join;
    private $_where;
    private $_limit;
    
    private $_values = [];
    
    public function setSelect(string $table, array $cols = NULL)
    {
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
    
    public function setJoin(array $j)
    {
        if(array_key_exists('table', $j)
                && array_key_exists('on', $j)
                && array_key_exists('type', $j)
                && !empty($j['table'])
                && !empty($j['on'])
                && !empty($j['type']))
        {
            $this->_join = ' ' . $j['type'] . ' JOIN ' . $j['table'] . ' ON ' . $j['on'];
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
    
    public function setLimit(int $i)
    {
        $this->_limit = ' LIMIT ' . $i;
    }
    
    public function create()
    {
        return $this->_select . $this->_join . $this->_where . $this->_limit;
    }
    
    public function getValues()
    {
        return $this->_values;
    }
}

