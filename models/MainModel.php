<?php

class MainModel
{
            
    const INT_RULE_NAME = 'int';
    
    const REQUIRED_RULE_NAME = 'required';
    
    const ALPHA_RULE_NAME = 'alpha';
    
    const STRING_RULE_NAME = 'string';
    
    /**
     * @var array | null 
     */
    private $_errors = null;
    
    /**
     *Хранит подключение к DB
     * @var object | null 
     */
    public $DB = null;

    /**
     * Сохраняет подключение
     */
    public function __construct()
    {
        $this->DB = new DB();
    }
    
    /**
     * Возвращает ошибки
     * @return type
     */
    public function validationErrors()
    {
        return $this->_errors;
    }
    
    /**
     * Метод пустышка
     * Будет использоваться когда клиент не определит функцию rules
     * @return array
     */
    protected static function rules()
    {
        return array();
    }
    
    /**
     * Стартовый метод с которого начитается валидация
     * @return bool
     */
    public function validate()
    {
//        пытаемся взять правила у потомка
        return $this->hasErrors(static::rules()) ? false : true;
    }
    
    /**
     * Возвращает массив сообщений об ошибках
     * @return array
     */
    private static function getMessages()
    {
        return array(
            self::INT_RULE_NAME => 'Не является числом',
            self::REQUIRED_RULE_NAME => 'Не может быть пустым',
            self::ALPHA_RULE_NAME => 'Поле не должно содержать ничего кроме a-zA-Z',
            self::STRING_RULE_NAME => 'Поле не является строкой',
        );
    }
    
    /**
     * Возвращает одно сообщение на основании типа проверки
     * @return string | bool
     */
    private function getMessage($type)
    {
        $allMessages = self::getMessages();
        
        if(array_key_exists($type, $allMessages))
        {
            return $allMessages[$type];
        }
        
        return false;
    }
    
    /**
     * Возвращает массив ошибок
     * 
     * @param int | string | bool $value
     * @param array $rules Массив правил со свойствами
     * @return boolean
     */
    private function hasErrors($rules)
    {
        $errors = array();
        
        foreach ($rules as $prop => $rulesString)
        {
            $r = explode('|', $rulesString);
            
            foreach ($r as $rule)
            {
                if($this->checkError($this->$prop, $rule) !== true)
                {
                    $errors[$prop] = $this->getMessage($rule);
                }
            }
        }
//        если есть ошибки возвращаем true
        if(count($errors) > 0)
        {
//            сохраняем все ошибки в одном месте
            $this->_errors = $errors;
            return true;
        }
        
        return false;
    }
    
    /**
     * @param string | int $value
     * @param string $rule
     * @return bool
     */
    private function checkError($value, $rule)
    {
//        если правило по которому будем проверять не найдено
//        тогда ничего не делаем
        switch($rule)
        {
            case self::INT_RULE_NAME: return self::isInt($value);
            case self::REQUIRED_RULE_NAME: return self::isRequired($value);
            case self::ALPHA_RULE_NAME: return self::isAlpha($value);
            case self::STRING_RULE_NAME: return self::isString($value);
            default: throw new Exception('Invalid argument');
        }
    }
    
    
    /*--  Методы проверок  --*/
    
    /**
     * Проверяет параметр на пустоту
     * 
     * @param type $value
     * @return boolean
     */
    private static function isRequired($var)
    {
        if(empty($var))
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Проверяет параметр на число
     * причем он может быть как стрингом так и интеджером
     * 
     * @param string | int $var
     * @return boolean
     */
    private static function isInt($var)
    {
        if(preg_match('/^[0-9]+$/', $var))
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверяет является ли параметр строкой
     * 
     * @param string $var
     * @return boolean
     */
    private static function isString($var)
    {
        if(is_string($var))
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверяет входит что-либо в сторку кроме символов латын.
     * 
     * @param string $var
     * @return boolean
     */
    private function isAlpha($var)
    {
        if(preg_match('/^[a-zA-Z]+$/', $var))
        {
            return true;
        }
        
        return false;
    }
    
    /*-- Методы проверок END --*/
}