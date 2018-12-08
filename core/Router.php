<?php

class Router
{
    private $_r = [
        'controller' => NULL,
        'action' => NULL,
    ];
    public function __construct()
    {
        $r = isset($_GET['r']) ? $_GET['r'] : '';
        
        if(preg_match("/^(?P<controller>[a-z]+)\/(?P<action>[a-z]+)$/", $r, $matches))
        {
            $this->_r['controller'] = $matches['controller'];
            $this->_r['action'] = $matches['action'];
        }
        elseif(preg_match("/^(?P<controller>[a-z]+)\/?$/", $r, $matches))
        {
            $this->_r['controller'] = $matches['controller'];
        }
    }
    
    public function getController()
    {
        return $this->_r['controller'];
    }
    
    public function getAction()
    {
        return $this->_r['action'];
    }
}
