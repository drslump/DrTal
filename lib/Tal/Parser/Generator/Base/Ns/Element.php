<?php

namespace DrSlump\Tal\Parser\Generator\Base\Ns;

use DrSlump\Tal;

abstract class Element
{
    protected $parser;
    protected $ns;
    protected $name;
    protected $attributes;
    protected $empty;
    
    public function __construct( Tal\Parser $parser, Tal\Parser\Generator\Base\Ns $ns, $name, $isEmpty = false )
    {
        $this->parser = $parser;
        $this->ns = $ns;
        $this->name = $name;
        $this->empty = $isEmpty;
        $this->attributes = array();
    }
    
    public function getName()
    {
        return substr( $this->name, (int)strpos($this->name, ':') );
    }
    
    public function getPrefix()
    {
        return substr( $this->name, 0, (int)strpos($this->name, ':') );
    }
    
    public function getParser()
    {
        return $this->parser;
    }

    public function getWriter()
    {
        return $this->getParser()->getWriter();
    }
    
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    public function getAttribute( $name )
    {
        return $this->attributes[$name];
    }
    
    public function setAttribute( $class, $name, $value, $escape = true )
    {
        $this->attributes[$name] = new $class( $this, $name, $value, $escape );
    }
    
    public function getEmpty()
    {
        return $this->empty;
    }
    
    public function setEmpty( $isEmpty )
    {
        $this->empty = $isEmpty;
    }
    
    public function runBeforeElement()
    {
        foreach ($this->attributes as $attr)
        {
            $attr->beforeElement();    
        }
    }

    public function runBeforeContent()
    {
        foreach ($this->attributes as $attr)
        {
            $attr->beforeContent();    
        }        
    }
    
    public function runAfterContent()
    {
        foreach ($this->attributes as $attr)
        {
            $attr->afterContent();    
        }        
    }
    
    public function runAfterElement()
    {
        foreach ($this->attributes as $attr)
        {
            $attr->afterElement();    
        }        
    }
    
    protected function getAttributesString()
    {
        $xml = '';
        foreach ( $this->attributes as $attr ) {
            if ( $attr->getRemoved() ) {
                continue;
            }
            
            $xml .= ' '; 
            
            if ( $attr->getPrefix() ) {
                $xml .= $attr->getPrefix() . ':';
            }
            
            $xml .= $attr->getName() . '="';
            
            if ($attr->getEscape())
                $xml .= htmlentities($attr->getValue());
            else
                $xml .= $attr->getValue();
            
            $xml .= '"';
        }
        
        return rtrim($xml);
    }
    
    public function start()
    {
    }
    
    function end()
    {
    }

}