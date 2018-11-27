<?php

class FileDriver implements DbDriverInterface
{
    const PATH = ROOT_DIR . DIRECTORY_SEPARATOR . 'file_store';

    private $_affectedRows = 0;

    private static $_instance = null;

    private function __construct()
    {
        if(!file_exists(self::PATH))
        {
            mkdir(self::PATH);
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
     * @return bool
     */
    public function insert(string $table, array $data)
    {
        $filename = self::PATH . DIRECTORY_SEPARATOR . $table . '.json';

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
        
        return true;
    }
        
    /**
     * Вставляет данные пачкой
     * 
     * @param string table
     * @param array data
     * @fix проработать вопрос создания файла и его существования
     */
    public function insertBatch(string $table, array $data)
    {
        $filename = self::PATH . DIRECTORY_SEPARATOR . $table . '.json';

        if(!file_exists($filename))
        {
            $f = fopen($filename, 'w');
            fwrite($f, json_encode($data));
            fclose($f);
        }
        elseif(filesize($filename) === 0)
        {
            $f = fopen($filename, 'w');
            fwrite($f, json_encode($data));
            fclose($f);
        }
        else
        {
            $root = json_decode(file_get_contents($filename), true);

            $f = fopen($filename, 'w');
            $resultarray = array_merge($root, $data);
            fwrite($f, json_encode($resultarray));
            fclose($f); 
        }
    }

    public function update(string $table, array $data, array $where)
    {
        $filename = self::PATH . DIRECTORY_SEPARATOR . $table . '.json';

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

    public function getOne(string $table, array $where)
    {
        $filename = self::PATH . DIRECTORY_SEPARATOR . $table . '.json';

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

    public function getAll(string $table)
    {
        $filename = self::PATH . DIRECTORY_SEPARATOR . $table . '.json';

        if(!file_exists($filename))
        {
            throw new Exception("Таблицы не существует");
        }

        $all = json_decode(file_get_contents($filename), true);

        return $all?: array();
    }

    public function getWhere(string $table, array $where)
    {
        $filename = self::PATH . DIRECTORY_SEPARATOR . $table . '.json';

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

    public function clear(string $table)
    {
        $filename = self::PATH . DIRECTORY_SEPARATOR . $table . '.json';

        if(!file_exists($filename))
        {
            throw new Exception("Таблицы не существует");
        }

        $f = fopen($filename, 'w');
        fclose($f);
    }
    
    public function delete(string $table, array $where)
    {
        $filename = self::PATH . DIRECTORY_SEPARATOR . $table . '.json';

        if(!file_exists($filename))
        {
//            Logger::getInstance()->write('Такой таблицы не существует'); // :FIX
            return false;
        }
        
//        получаем содержимое файла
        $d = file_get_contents($filename);
//        если пустой 
        if(empty($d))
        {
           $this->_affectedRows = 0;
           return true;
        }
        
//        декодируем 
        $i = json_decode($d, true);
        
//        если не массив бросаем ошибку
        if(!is_array($i))
        {
//            Logger::getInstance()->write('Не удалось прочитать таблицу'); // :FIX
            return false;
        }
        
        foreach ($i as $k => $row)
        {
//            число совпадений where
            $upd = 0;
            
            foreach($where as $col => $condition)
            {
                if(isset($row[$col]) && $row[$col] == $condition)
                {
                    $upd++;
                }
            }
//            если число совпадений равно общему количеству условий удаляем
            if($upd === count($where))
            {
                unset($i[$k]);
            }
        }
        
//        перезаписываем целиком файл
        $f = fopen($filename, 'w');
        fwrite($f, json_encode(array_values($i)));
        fclose($f);
        
        return true;
    }
    
    public function replace(string $table, array $where, array $dataRow)
    {
        $filename = self::PATH . DIRECTORY_SEPARATOR . $table . '.json';

        if(!file_exists($filename))
        {
//            Logger::getInstance()->write('Такой таблицы не существует'); // :FIX
            return false;
        }

//        формируем массив условий по которым будем удалять
        $newWhere = array();

        foreach ($where as $v)
        {
            if(!array_key_exists($v, $dataRow))
            {
//                Logger::getInstance()->write('Условия where нет в заменяющей строке'); // :FIX
                return false;
            }

            $newWhere[$v] = $dataRow[$v];
        }

        $this->delete($table, $newWhere);
        $this->insert($table, $dataRow);
        return true;
    }
}