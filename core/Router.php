<?php

class Router
{
    private $_r = [
        'controller' => NULL,
        'action' => NULL,
    ];
    
    public $hasRoute = FALSE;
    
    public function __construct()
    {
        $r = isset($_GET['r']) ? $_GET['r'] : '';
        
        if(preg_match("/^(?P<controller>[a-z]+)\/(?P<action>[a-z]+)$/", $r, $matches))
        {
            $this->_r['controller'] = $matches['controller'];
            $this->_r['action'] = $matches['action'];
            $this->hasRoute = TRUE;
        }
        elseif(preg_match("/^(?P<controller>[a-z]+)\/?$/", $r, $matches))
        {
            $this->hasRoute = TRUE;
            $this->_r['controller'] = $matches['controller'];
        }
        elseif(preg_match("/^$/", $r))
        {
            $this->hasRoute = TRUE; 
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
