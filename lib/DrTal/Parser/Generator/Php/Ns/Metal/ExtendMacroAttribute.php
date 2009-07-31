<?php

namespace DrTal::Parser::Generator::Php::Ns::Metal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';

class ExtendMacroAttribute extends DrTal::Parser::Generator::Base::Attribute
{
    
    public function beforeElement()
    {
        // Search for metal:define-macro in this same element
        $define = false;
        foreach( $this->element->getAttributes() as $attr ) {
            if ( $this->getPrefix() === $attr->getPrefix() && $attr->getName() === 'define-macro' ) {
                $define = $attr;
                break;
            }
        }
        
        if (!$define) {
            throw new DrTal_Parser_Exception( $this->name . ' must be used in conjunction with ' . $this->getPrefix() . ':' . 'define-macro' );
        }
        
        throw new DrTal_Parser_Exception("Extend-macro is quite complicated to implement so it's not finished yet");        
    }    
    
}