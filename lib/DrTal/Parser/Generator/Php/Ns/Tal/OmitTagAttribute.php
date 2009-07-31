<?php

namespace DrTal::Parser::Generator::Php::Ns::Tal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';

class OmitTagAttribute extends DrTal::Parser::Generator::Base::Attribute
{
    public function beforeElement()
    {
        if ( empty($this->value) ) {
            $this->getCodegen()->capture();
        }
    }
    
    public function beforeContent()
    {
        if ( empty($this->value) ) {
            $this->getCodegen()->endCapture();
        }
    }
    
    public function afterContent()
    {
        if ( empty($this->value) ) {
            $this->getCodegen()->capture();
        }        
    }
    
    public function afterElement()
    {
        if ( empty($this->value) ) {
            $this->getCodegen()->endCapture();
        }
    }
}