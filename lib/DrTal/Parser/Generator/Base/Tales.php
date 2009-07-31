<?php

namespace DrTal::Parser::Generator::Base;

namespace DrTal::Parser::Generator::Base;

class Tales {
    
    protected $_writer;
    protected $_exp;
    
    
    public function __construct( $writer, $exp )
    {
        $this->_writer = $writer;
        $this->_exp = $exp;
    }
    
    public function getExpression()
    {
        return $this->_exp;
    }
    
    public function isFinished()
    {
        return trim($this->_exp) === '';
    }
    
    public function isSelfContained()
    {
        return true;
    }
    
    abstract public function evaluate();
}
