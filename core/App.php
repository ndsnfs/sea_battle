<?php
class App
{
    public static $serv;
    
    private $_router;
    
    private $_controllerName = 'Battle';
    
    private $_actionName = 'index';
    
    public function init($config)
    {
        self::$serv = new ServiceLocator();
        $this->_router = new Router();
        $this->_controllerName = $this->_router->getController() ? $this->_router->getController() : $this->_controllerName;
        $this->_actionName = $this->_router->getAction() ? $this->_router->getAction() : $this->_actionName;
    }
    
    /**
     * Загружает указанный сервис
     * @param string $serviceName
     */
    public function load(string $serviceName)
    {
        if($this->load($serviceName))
        {
            return true;
        }
        
        return false;
    }
    
    public function run()
    {
//        :FIX Проверка существования файла
        $c = ucfirst($this->_controllerName);
        $a = $this->_actionName;
        
        (new $c)->$a();
    }
}

