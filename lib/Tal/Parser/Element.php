<?php

namespace DrSlump\Tal\Parser;

use DrSlump\Tal;
use DrSlump\Tal\Parser\OpcodeList;

abstract class Element
{
    protected $_parser;
    protected $_ns;
    protected $_name;
    protected $_attributes;
    protected $_empty;
    
    public function __construct( Tal\Parser $parser, Tal\Parser\Ns $ns, $name, $isEmpty = false )
    {
        $this->_parser = $parser;
        $this->_ns = $ns;
        $this->_name = $name;
        $this->_empty = $isEmpty;
        $this->_attributes = array();
    }
    
    public function getName()
    {
        return substr( $this->_name, (int)strpos($this->_name, ':') );
    }
    
    public function getPrefix()
    {
        return substr( $this->_name, 0, (int)strpos($this->_name, ':') );
    }
    
    public function getParser()
    {
        return $this->_parser;
    }

    public function getProgram()
    {        
        return $this->getParser()->getProgram();
    }
    
    public function getAttributes()
    {
        return $this->_attributes;
    }
    
    public function getAttribute( $name )
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }
    
    public function setAttribute( Tal\Parser\Attribute $obj )
    {
        $this->_attributes[$obj->getRawName()] = $obj;
    }
    
    public function isEmpty($isEmpty = null)
    {
        if (null !== $isEmpty) {
            $this->_empty = $isEmpty;
        }
        
        return $this->_empty;
    }
    
    public function beforeElement()
    {
        foreach ($this->_attributes as $attr)
        {
            if (!($attr instanceof Tal\Parser\Ns\Xml\AnyAttribute)) {
                if (Tal::debugging()) {
                    $this->getProgram()
                    ->comment('[BE] ' . $attr->getPrefix() . ':' . $attr->getName() . ' = ' . $attr->getValue());
                } else {
                    $this->getProgram()
                    ->comment($attr->getPrefix() . ':' . $attr->getName() . ' = ' . $attr->getValue());                    
                }
            }
            
            $attr->beforeElement();    
        }
    }

    public function beforeContent()
    {
        foreach ($this->_attributes as $attr)
        {
            if (Tal::debugging() && !($attr instanceof Tal\Parser\Ns\Xml\AnyAttribute)) {
                $this->getProgram()
                ->comment('[BC] ' . $attr->getPrefix() . ':' . $attr->getName() . ' = ' . $attr->getValue());
            }

            $attr->beforeContent();    
        }        
    }
    
    public function afterContent()
    {
        foreach ($this->_attributes as $attr)
        {
            if (Tal::debugging() && !($attr instanceof Tal\Parser\Ns\Xml\AnyAttribute)) {
                $this->getProgram()
                ->comment('[AC] ' . $attr->getPrefix() . ':' . $attr->getName() . ' = ' . $attr->getValue());
            }
            
            $attr->afterContent();    
        }        
    }
    
    public function afterElement()
    {
        foreach ($this->_attributes as $attr)
        {
            if (Tal::debugging() && !($attr instanceof Tal\Parser\Ns\Xml\AnyAttribute)) {
                $this->getProgram()
                ->comment('[AE] ' . $attr->getPrefix() . ':' . $attr->getName() . ' = ' . $attr->getValue());
            }

            $attr->afterElement();            
        }        
    }
    
    protected function getAttributesOpcodes()
    {
        $ops = new Tal\Parser\OpcodeList();
        
        foreach ( $this->_attributes as $attr ) {
            if ($attr->isRemoved()) {                
                continue;
            }

            $ops->xml(' ');
            
            if ($attr->getPrefix()) {
                $ops->xml( $attr->getPrefix() . ':' );
            }
            
            $ops->xml( $attr->getName() . '="' );
            
            $value = $attr->getValue();
            if ($value instanceof Tal\Parser\OpcodeList) {
                $ops->appendList($value);
            } else {
                $ops->xml( htmlspecialchars($value) );
            }
            
            $ops->xml('"');
        }
        
        return $ops;
    }
    
    public function open()
    {
    }
    
    function close()
    {
    }

}