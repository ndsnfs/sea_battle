<?php

class PgsqlDriver implements DbDriverInterface
{
    /**
     * @var PDOStatement | null
     */
    private $_stm;
    
    /**
     * Хранит в строковом виде последний запрос
     * @var string
     */
    private $_lastQuery;
    
    /**
     * DB Connection
     * @var object 
     */
    private $_pdo;
    
    /**
     * Экземпляр PgsqlDriver
     * @var object
     */
    private static $_instance;
    
    /**
     * Хранит массив массивов вида [['table' => $tbName, 'on' => $on, 'type' => $type]]
     * @var array
     */
    private $_join = [];
    
    /**
     * Временно сохраняет, очищает, и возвращает данные связанные с присоединяемой таблицей
     * @return array
     */
    private function _getJoin()
    {
        $tmpJoin = $this->_join;
        $this->_join = [];
        
        return $tmpJoin;
    }
    
    private function __construct() {
        $dsn = 'pgsql:dbname=sea_battle;host=127.0.0.1';
        $username = 'sea_battle';
        $password = 'qwerty';
        
        try
        {
            $this->_pdo = new PDO($dsn, $username, $password);
        }
        catch (PDOException $e)
        {
            echo 'Db conn error' . $e->errorInfo(); exit;
        }
    }
    
    private function __clone() {}
    
    public static function getInstance()
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self;
        }

        return self::$_instance;
    }
        
    /**
     * Проверяет имеются ли в драйвере ошибки
     * @return bool
     */
    public function hasErrors()
    {
        $pdo = $this->_pdo->errorInfo();
        $stm = $this->_stm->errorInfo();
        
        if($pdo[0] !== '00000' || $stm[0] !== '00000')
        {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Возвращает код ошиби
     */
    public function errorCode()
    {
        if($this->_pdo->errorCode())
        {
            return $this->_pdo->errorCode();
        }
        elseif($this->_stm->errorCode())
        {
            return $this->_stm->errorInfo();
        }
        else
        {
            return NULL;
        }
    }
    
    /**
     * Возвращает информацию об ошибке
     */
    public function errorInfo()
    {
        if($this->_pdo->errorInfo())
        {
            return $this->_pdo->errorInfo();
        }
        elseif($this->_stm->errorInfo())
        {
            return $this->_stm->errorInfo();
        }
        else
        {
            return NULL;
        }
    }
    
    /**
     * Возвращает последний sql запрос
     * @return string
     */
    public function lastQuery()
    {
        return $this->_lastQuery;
    }
    
    /**
     * Возвращает ид последней вставки
     * @param string $coll
     * @return string
     */
    public function lastInsertId(string $coll)
    {
        return $this->_pdo->lastInsertId($coll);
    }
    
    /**
     * Формирует данные для JOIN
     * 
     * @param string $table Название присоединяемой таблицы
     * @param string $on Условие присоединения
     * @param string $type Тип присоединения
     */
    public function join(string $table, string $on, string $type)
    {
        $this->_join[] = ['table' => $table, 'on' => $on, 'type' => $type];
    }
    
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
    
    /**
     * Вставляет одну запись в "таблицу"
     * 
     * @param string $table
     * @param array $set
     */
    public function insert(string $table, array $set)
    {
        $qc = new QueryCreator();
        $qc->setInsert($table, $set);
        
        $this->_lastQuery = $qc->create();
        $this->_stm = $this->_pdo->prepare($qc->create());
        
        if($this->_stm->execute($qc->getValues()))
        {
            return true;
        }
        
        return false;
    }

    /**
     * Вставляет одну и более строк в "таблицу"
     * 
     * @param string $table
     * @param array $data
     */
    public function insertBatch(string $table, array $data)
    {
        
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
        $this->_stm = $this->_pdo->prepare($sql);
        
//        :FIX не работает транзакция
//        $this->_pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
//        $this->_pdo->beginTransaction();
        
//        вставляем проверенные данные
        foreach ($values as $row)
        {
            $this->_stm->execute($row);
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
     * @param array $set
     * @param array $where
     */
    public function update(string $table, array $set, array $where)
    {
        $qc = new QueryCreator();
        $qc->setUpdate($table, $set);
        $qc->setWhere($where);
        
        $this->_lastQuery = $qc->create();
        
//        пытаемся подготовить запрос
        $this->_stm = $this->_pdo->prepare($qc->create());
        
        if($this->_stm->execute($qc->getValues()))
        {
            return true;
        }
        
        return false;
    }

    /**
     * Возващает одну строку по условию
     * 
     * @param string $table
     * @param array $where
     * @param array $cols
     */
    public function getOne(string $table, array $where, array $cols = [])
    {        
        $qc = new QueryCreator();
        $qc->setSelect($table, $cols);
        $qc->setJoin($this->_getJoin());
        $qc->setWhere($where);
        $qc->setLimit(1);

        $this->_lastQuery = $qc->create();
        
        $this->_stm = $this->_pdo->prepare($qc->create());
        
        if($this->_stm->execute($qc->getValues()))
        {
            $fetch = $this->_stm->fetch(PDO::FETCH_ASSOC);
            $res = [];
            
            if($fetch !== FALSE)
            {
                $res = $fetch;
            }
            
            return $res;
        }       
        
        return false;
    }

    /**
     * Возвращает все строки "таблицы"
     * 
     * @param string $table
     * @param array $cols Массив столбцов, кот. нужно выбрать
     */
    public function getAll(string $table, $cols = [])
    {
        $qc = new QueryCreator();
        $qc->setSelect($table, $cols);
        $qc->setJoin($this->_getJoin());
                
        if($this->_stm = $this->_pdo->query($qc->create()))
        {
            return $this->_stm->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return false;
    }

    /**
     * Возвращает массив строк "таблицы" по условию
     * 
     * @param string $table
     * @param array $where
     * @param array $cols Необязательный параметр - столбцы которые необх. выбрать
     */
    public function getWhere(string $table, array $where, array $cols = [])
    {
        $qc = new QueryCreator();
        $qc->setSelect($table, $cols);
        $qc->setJoin($this->_getJoin());
        $qc->setWhere($where);
        
        $this->_lastQuery = $qc->create();
        $this->_stm = $this->_pdo->prepare($qc->create());
        
        if($this->_stm->execute($qc->getValues()))
        {
            return $this->_stm->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $this->errorCode = $this->_stm->errorCode();
        $this->errorInfo = $this->_stm->errorInfo();
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
        $qc = new QueryCreator();
        $qc->setDelete($table);
        $qc->setWhere($where);
        
        $this->_lastQuery = $qc->create();
        
        $this->_stm = $this->_pdo->prepare($qc->create());
        
        if($this->_stm->execute($qc->getValues()))
        {
            return true;
        }
        
        return false;
    }

    /**
     * Делает delete + insert
     * Если удовлетворяющей $value записи нет - просто вставляет запись
     * 
     * @param string $table
     * @param array $set Столбецы которые заполняем - все в таблице
     * @param array $where строки которые удаляем
     */
    public function replace(string $table, array $set, array $where)
    {        
        $this->_pdo->beginTransaction();
        
        if($this->delete($table, $where) && $this->insert($table, $set))
        {
            $this->_pdo->commit();
            return true;
        }
        
        $this->_pdo->rollBack();
        return false;
    }
}