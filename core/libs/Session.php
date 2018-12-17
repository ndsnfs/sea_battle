<?php

class Session
{
    /**
     * Стартует сессию
     */
    public function __construct()
    {
        session_start();
    }
    
    /**
     * Добавляет значение в сессию
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name, $value)
    {
        $_SESSION[$name] = $value;
    }
    
    /**
     * Получаем значение сессии
     * @param string $name
     * @return mixed | NULL
     */
    public function get(string $name)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : NULL;
    }
    
    /**
     * Удаляет значение сессии
     * @param string $name
     * @return boolean
     */
    public function delete(string $name)
    {
        if(isset($_SESSION[$name]))
        {
            unset($_SESSION[$name]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверяет имеется ли сессия
     * @param string $name
     */
    public function has(string $name)
    {
        return isset($_SESSION[$name]) ? TRUE : FALSE;
    }
}

