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
}

