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
        if(!$this->_router->hasRoute)
        {
//            :FIX организовать загрузку из конфига
            (new NotFound())->index();
        }
        else
        {
            $file = ucfirst($this->_controllerName);
            $fileName = 'controllers/' . $file . '.php';
            
            if(file_exists($fileName) && method_exists($file, $this->_actionName))
            {
                $c = ucfirst($file);
                $a = $this->_actionName;

                (new $c)->$a();
            }
            else
            {
//              :FIX организовать загрузку из конфига
                (new NotFound())->index();
            }
        }
    }
}

