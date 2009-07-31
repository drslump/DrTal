<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DrTal.php';

/*
 Class: PHPTAL
    Emulates PHPTAL class syntax to ease the transition to DrTal for existing projects.
 
 */
class PHPTAL
{
    public function __construct( $path = false )
    {
        $this->setTemplate($path);        
    }
    
    public function setTemplate( $path )
    {
        $this->path = $path;
    }
    
    public function setSource( $src, $path=false )
    {
        
    }
    
    public function setTemplateRepository($rep)
    {
        
    }
    
    public function stripComments($bool)
    {
        
    }
    
}