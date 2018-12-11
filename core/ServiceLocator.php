<?php

class ServiceLocator
{
    private $_container = [];
    
    private $_defaultServices = ['input', 'logger'];
    
    public function __construct()
    {
//        :FIX приходящий конфиг
        $config = ['session'];
        
        $all = array_merge($this->_defaultServices, $config);
        
        foreach ($all as $service)
        {
//         :FIX проверить существование файла   
            $s = ucfirst($service);
            $this->_container[$service] = new $s;
        }
    }
    
    public function __get($serviceName)
    {
        if(array_key_exists($serviceName, $this->_container))
        {
            return $this->_container[$serviceName];
        }
        
        return NULL;
    }
    
    /**
     * 
     * @param string $serviceName
     */
    public function load(string $serviceName)
    {
        $class = ucfirst($serviceName);
//        загружаем только из libs
        $file = 'core/libs/' . $class . '.php';
        
        if(file_exists($file))
        {
            require_once $file;
            
            if(class_exists($class))
            {
                $this->_container[$serviceName] = new $class;
            }
            else
            {
//                :FIX залогировать или Exception
                return false;
            }
        }
        else
        {
//            :FIX залогировать или Exception
            return false;
        }
    }
}

