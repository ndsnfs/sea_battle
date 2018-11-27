<?php

interface DbDriverInterface
{
	public function insert(string $table, array $data);
	public function insertBatch(string $table, array $data);
	public function update(string $table, array $data, array $where);
	public function getOne(string $table, array $data);
	public function getAll(string $table);
	public function getWhere(string $table, array $data);
        
	public function clear(string $table);
        
        /**
         * Удаляет из хранилища одну или несколько записей
         *
         * @param string $table
         * @param array $where
         */
        public function delete(string $table, array $where);

        /**
         * Делает delete + insert
         * Если удовлетворяющей $value записи нет - просто вставляет запись
         * 
         * @param string $table
         * @param array $where Столбец(ы) по которому ищем
         * @param array $data
         */
        public function replace(string $table, array $where, array $dataRow);
}