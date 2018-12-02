<?php

class MainModel
{ 
    const INT_RULE_NAME = 'int';
    
    const REQUIRED_RULE_NAME = 'required';
    
    const ALPHA_RULE_NAME = 'alpha';
    
    const STRING_RULE_NAME = 'string';

    const PREG_MATH_RULE_NAME = 'pregMath';
    
    const CUSTOM_RULE_NAME = 'custom_';
    
    /**
     * @var array
     */
    public $errors;
    
    public $customErrors = array();
    
    /**
     *Хранит подключение к DB
     * @var object
     */
    public $DB;

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
        return $this->errors;
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
            self::PREG_MATH_RULE_NAME => 'Не соответствует шаблону',
            self::CUSTOM_RULE_NAME => 'Не прошла Custom функция',

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
     * Заполняет массив ошибками если они есть
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
                $params = array();
//                пробуем распарсить правила на наличие функции в правиле
//                например pregMath[PATTERN]
                $c_rule = static::parseRule($rule);
                
//                и если она есть, разделяем ее имя и параметры
                if(is_array($c_rule))
                {
                    $rule = $c_rule[0]; // строка
                    $params = $c_rule[1]; // массив
                }
                
                if($this->checkError($this->$prop, $rule, $params) !== true)
                {
                    if(isset($params['func'])
                            && array_key_exists($params['func'], $this->customErrors)
                            && is_array($this->customErrors[$params['func']]))
                    {
                        foreach ($this->customErrors[$params['func']] as $errMsg)
                        {
                            $errors[$prop][] = $errMsg;
                        }
                    }
                    else
                    {
                        $errors[$prop][] = $this->getMessage($rule);
                    }
                }
            }
        }
//        если есть ошибки возвращаем true
        if(count($errors) > 0)
        {
//            сохраняем все ошибки в одном месте
            $this->errors = $errors;
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверяет конкретное значение на конкретное правило
     * 
     * @param string | int $value
     * @param string $rule
     * @return bool
     * @throws Exception Может бросить да да
     */
    private function checkError($value, $rule, $params = array())
    {
//        if(is_array($value))
//        {
//            foreach ($value as $v)
//            {
//                $this->checkError($v, $rule, $params);
//            }
//        }
        
//        если правило по которому будем проверять не найдено
//        тогда бросаем исключение
        switch($rule)
        {
            case self::INT_RULE_NAME: return self::isInt($value);
            case self::REQUIRED_RULE_NAME: return self::isRequired($value);
            case self::ALPHA_RULE_NAME: return self::isAlpha($value);
            case self::STRING_RULE_NAME: return self::isString($value);
            case self::PREG_MATH_RULE_NAME: return self::hasMath($value, $params);
            case self::CUSTOM_RULE_NAME: return $this->runCustom($value, $params);
            default: throw new Exception('Invalid argument');
        }
    }
    
    
    /*--  Методы проверок  --*/
    
    /**
     * Проверяет параметр на пустоту
     * 
     * @param mixed $var
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
        if(preg_match('/^[0-9]+$/', (string)$var))
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
    
    /**
     * Проверяет находится ли вхождение в строке
     * @param string $value
     * @param array $params
     * @return boolean
     * @throws Exception Может бросить да да
     */
    private static function hasMath($value, $params)
    {
        if(!array_key_exists('pattern', $params))
        {
            throw new Exception('Invalid argument');
        }
        
        if(preg_match("/" . $params['pattern'] . "/", $value))
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * Запускает custom метод объекта наследника
     */
    protected function runCustom($value, $params)
    {
        if(!array_key_exists('func', $params))
        {
            throw new Exception('Invalid argument');
        }
        
        if(method_exists($this, $params['func']))
        {
            return $this->{$params['func']}($value);
        }
        
        return false;
    }
    
    /*-- Методы проверок END --*/
    
    
    
    /*-- Вспомогательные функции --*/
    
    /**
     * @param string $rule
     * @return array | string
     */
    private static function parseRule($rule)
    {
        if(preg_match("/^(" . self::PREG_MATH_RULE_NAME . ")\[(.*)\]$/", $rule, $arr))
        {
            $value = $arr[1];
            $pattern = $arr[2];
            
            return array($value, array('pattern' => $pattern)); // :FIX - нарушение принципа
        }
        elseif(preg_match("/^(" . self::CUSTOM_RULE_NAME . ")(.*)$/", $rule, $arr))
        {
            $value = $arr[1];
            $func = $arr[2];   
            
            return array($value, array('func' => $func)); // :FIX - нарушение принципа
        }
        else
        {
            return $rule;
        }
    }
    
    /*-- Вспомогательные функции END --*/
}