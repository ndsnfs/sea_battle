<?php

class PgsqlDriver implements DbDriverInterface
{
    private $_pdo;
    public $errorInfo;
    public $errorCode;
    private static $_instance;
    
    private function __construct() {
        $dsn = 'pgsql:dbname=sea_battle;host=127.0.0.1';
        $username = 'sea_battle';
        $password = 'qwerty';
        
        try {
            $this->_pdo = new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            echo 'Db conn error' . $e->errorInfo(); exit;
        }
    }
    
    private function __clone() {}
    
    /**
     * Запускает транзакцию
     */
    public function transBegin()
    {
        $this->_pdo->beginTransaction();
    }
    
    /**
     * Фиксирует транзакцию
     */
    public function transCommit()
    {
        $this->_pdo->commit();
    }
    
    /**
     * Откат изменений
     */
    public function transRollback()
    {
        $this->_pdo->rollBack(); 
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
     * Вставляет одну запись в "таблицу"
     * 
     * @param string $table
     * @param array $data
     */
    public function insert(string $table, array $data){
        
//        :FIX запросить столбцы таблицы(белый список) $table
        $coolStr = implode(', ', array_keys($data));
        $ques = implode(', ', array_fill(0, count($data), '?'));
        $values = array_values($data);
        
        $sql = 'INSERT INTO ' . $table . ' (' . $coolStr . ') VALUES ( ' . $ques . ')';
        $stm = $this->_pdo->prepare($sql);
        
        if($stm->execute($values))
        {
            return true;
        }
//        :FIX переделать
        $this->errorInfo = $stm->errorInfo();
        $this->errorCode = $stm->errorCode();
        return false;
    }

    /**
     * Вставляет одну и более строк в "таблицу"
     * 
     * @param string $table
     * @param array $data
     */
    public function insertBatch(string $table, array $data){
        
//        :FIX запросить столбцы таблицы(белый список) $table
//        получаем первую строку
        $firstRow = array_shift($data);
//        подготавливаем массив для вставки
//        ориентироваться будем по столбцам первой строки
        $values = array();
//        добавляем в массив данные первой строки
        array_push($values, array_values($firstRow));
//        получаем массив столбцов для последующей проверки
        $coolsArr = array_keys($firstRow);
//        формируем строку столбцов
        $coolStr = implode(', ', $coolsArr);
//        формируем строку плейсхолдеров
        $plaveholders = implode(', ', array_fill(0, count($firstRow), '?'));
        
        foreach ($data as $row)
        {
//            если длины массивов первой строки и последующих разные вываливаемся
            if(count($row) !== count($coolsArr))
            {
                return false;
            }
            
//            проверяем ключи в строке $row
            foreach ($coolsArr as $col)
            {
//                если столбца из первой строки нет в последующих вываливаемся
                if(!array_key_exists($col, $row))
                {
                    return false;
                }
            }
//            подготавливаем данные для вставки
            array_push($values, array_values($row));
        }
        
        $sql = 'INSERT INTO ' . $table . ' (' . $coolStr . ') VALUES ( ' . $plaveholders . ')';
//        пытаемся подготовить запрос
        $stm = $this->_pdo->prepare($sql);
        
//        $this->_pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
//        $this->_pdo->beginTransaction();
        
//        вставляем проверенные данные
        foreach ($values as $row)
        {
            $res = $stm->execute($row);
        }
                
        if(true) // :FIX проверить на наличие ошибок
        {
//            $this->_pdo->commit();
            return true;  
        }
        
//        $this->_pdo->rollBack();
        return false;
    }

    /**
     * Обновляет "таблицу" значениями $data по условию $where
     * 
     * @param string $table
     * @param array $data
     * @param array $where
     */
    public function update(string $table, array $data, array $where){
        
//        :FIX проверить ключи(колонки) $data и $where на наличие их в information_shema
        $set = ''; // :FIX сделать как в where
        $setterCools = array_keys($data);
        $values = [];
        
        foreach ($setterCools as $col)
        {
            if(end($setterCools) === $col)
            {
                $set .= ' ' . $col . ' = ?';
            }
            else
            {
                $set .= ' ' . $col . ' = ?,';
            }
            
            $values[] = $data[$col];
        }
        
        $sql = 'UPDATE ' . $table . ' SET ' . $set;
        
        if(is_array($where) && count($where) > 0)
        {
            $whereCools = array_keys($where);
            $firstWhereCol = array_shift($whereCools);
            $sql .= ' WHERE ' . $firstWhereCol . ' = ?';
            $values[] = $where[$firstWhereCol];
            
            foreach ($whereCools as $col)
            {
                $sql .= ' AND ' . $col . ' = ?';
                $values[] = $where[$col];
            }
        }
        
//        пытаемся подготовить запрос
        $stm = $this->_pdo->prepare($sql);
        
        if($stm->execute($values))
        {
            return true;
        }
        
//        :FIX записать ошибки
        return false;
    }

    /**
     * Возващает одну строку по условию
     * 
     * @param string $table
     * @param array $data
     */
    public function getOne(string $table, array $where)
    {        
//        :FIX проверить таблицу на существование (information_shema или белый список)
        $sql = 'SELECT * FROM ' . $table;
        
//        :FIX проверить столбцы на наличие в informaton_shema
        $cols = array_keys($where);
        $values = [];
        
        if(count($cols) > 0)
        {
            $first = array_shift($cols);
            $sql .= ' WHERE ' . $first . ' = ?';
            $values[] = $where[$first];
        
            foreach ($cols as $col)
            {
                $sql .= ' AND ' . $col . ' = ?';
                $values[] = $where[$col];
            }
        }
        
        $sql .= ' LIMIT 1';
        
        $stm = $this->_pdo->prepare($sql);
        
        if($stm->execute($values))
        {
            return $stm->fetch(PDO::FETCH_ASSOC);
        }
        
//        :FIX записать ошибки
        return false;
    }

    /**
     * Возвращает все строки "таблицы"
     * 
     * @param string $table
     */
    public function getAll(string $table)
    {
//        :FIX проверить таблицу на существование (information_shema или белый список)
        $sql = 'SELECT * FROM ' . $table;
                
        if($stm = $this->_pdo->query($sql))
        {
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
        
//        :FIX записать ошибки
        return false;
    }

    /**
     * Возвращает массив строк "таблицы" по условию
     * 
     * @param string $table
     * @param array $data
     */
    public function getWhere(string $table, array $where)
    {
//        :FIX проверить таблицу на существование (information_shema или белый список)
        $sql = 'SELECT * FROM ' . $table;
        
//        :FIX проверить столбцы на наличие в informaton_shema
        $cols = array_keys($where);
        $values = [];
        
        if(count($cols) > 0)
        {
            $first = array_shift($cols);
            $sql .= ' WHERE ' . $first . ' = ?';
            $values[] = $where[$first];
        
            foreach ($cols as $col)
            {
                $sql .= ' AND ' . $col . ' = ?';
                $values[] = $where[$col];
            }
        }
        
        $stm = $this->_pdo->prepare($sql);
        
        if($stm->execute($values))
        {
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
        
//        :FIX записать ошибки
        return false;
    }

    /**
     * Очищает "таблицу"
     * 
     * @param string $table
     */
    public function clear(string $table){
        
//        белый список или information_shema
        if($this->_pdo->exec('DELETE FROM ' . $table))
        {
            return true;
        }
        
//        :FIX записать ошибки
        var_dump($this->_pdo->errorInfo());
        return false;
    }

    /**
     * Удаляет из хранилища одну или несколько записей
     *
     * @param string $table
     * @param array $where
     */
    public function delete(string $table, array $where)
    {
//        :FIX проверить столбцы на наличие из в informaton_shema
        $cols = array_keys($where);
        $values = [];
        
        $sql = 'DELETE FROM ' . $table;
        
        if(count($cols) > 0)
        {
            $first = array_shift($cols);
            $sql .= ' WHERE ' . $first . ' = ?';
            $values[] = $where[$first];
        
            foreach ($cols as $col)
            {
                $sql .= ' AND ' . $col . ' = ?';
                $values[] = $where[$col];
            }
        }
        
        $stm = $this->_pdo->prepare($sql);
        
        if($stm->execute($values))
        {
            return true;
        }
        
//        :FIX записать ошибки
        return false;
    }

    /**
     * Делает delete + insert
     * Если удовлетворяющей $value записи нет - просто вставляет запись
     * 
     * @param string $table
     * @param array $where Столбец(ы) по которому ищем
     * @param array $dataRow Зменяющая строка должна содержать в себе все столбцы таблицы $table
     */
    public function replace(string $table, array $where, array $dataRow)
    {
//        :FIX получить столбцы таблицы information_shema
        
        $this->_pdo->beginTransaction();
        
        if($this->delete($table, $where) && $this->insert($table, $dataRow))
        {
            $this->_pdo->commit();
            return true;
        }
        
        $this->_pdo->rollBack();
        return false;
    }
}