<?php

namespace DrSlump\Tal\Parser\Ns\Metal;

use DrSlump\Tal\Parser;

class ExtendMacroAttribute extends Parser\Attribute
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
            throw new Parser\Exception( $this->name . ' must be used in conjunction with ' . $this->getPrefix() . ':' . 'define-macro' );
        }
        
        throw new Parser\Exception("Extend-macro is quite complicated to implement so it's not finished yet");        
    }    
    
}