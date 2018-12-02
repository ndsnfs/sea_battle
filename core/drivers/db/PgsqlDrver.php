<?php

class PgsqDriver implements DbDriverInterface
{
    private $_instance;
    
    private function __construct() {
        $this->_pdo = new PDO($dns, $username, $password);
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
     * Вставляет одну запись в "таблицу"
     * 
     * @param string $table
     * @param array $data
     */
    public function insert(string $table, array $data){
        
    }

    /**
     * Вставляет одну и более строк в "таблицу"
     * 
     * @param string $table
     * @param array $data
     */
    public function insertBatch(string $table, array $data){}

    /**
     * Обновляет "таблицу" значениями $data по условию $where
     * 
     * @param string $table
     * @param array $data
     * @param array $where
     */
    public function update(string $table, array $data, array $where){}

    /**
     * Возващает одну строку по условию
     * 
     * @param string $table
     * @param array $data
     */
    public function getOne(string $table, array $data){}

    /**
     * Возвращает все строки "таблицы"
     * 
     * @param string $table
     */
    public function getAll(string $table);

    /**
     * Возвращает массив строк "таблицы" по условию
     * 
     * @param string $table
     * @param array $data
     */
    public function getWhere(string $table, array $data){}

    /**
     * Очищает "таблицу"
     * 
     * @param string $table
     */
    public function clear(string $table){}

    /**
     * Удаляет из хранилища одну или несколько записей
     *
     * @param string $table
     * @param array $where
     */
    public function delete(string $table, array $where){}

    /**
     * Делает delete + insert
     * Если удовлетворяющей $value записи нет - просто вставляет запись
     * 
     * @param string $table
     * @param array $where Столбец(ы) по которому ищем
     * @param array $data
     */
    public function replace(string $table, array $where, array $dataRow){}
}