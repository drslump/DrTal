<?php

namespace DrSlump\Tal\Parser\Ns\Tal;

use DrSlump\Tal\Parser;
use DrSlump\Tal;

class ContentAttribute extends Parser\Attribute
{
    public function beforeElement()
    {
        // Makes sure the element is not empty
        $this->getElement()->isEmpty(false);
    }
    
    public function beforeContent()
    {
        // Start capturing the element's content, if any, for default value
        $this->getProgram()->capture('$_default');
    }
    
    public function afterContent()
    {
        $this->getProgram()->endCapture();
                
        // Get attribute's value
        $value = trim($this->getValue());
        
        // Check if we need to escape the result
        $escape = 'true';
        if (stripos($value, 'structure') === 0) {
            $escape = 'false';
            $value = substr( $value, strlen('structure') );
        }
        
        // Run tales expression to get value
        $this->doAlternates( $value, '$_content', '$_default' );
        
        // Finally echo the value
        $this->getProgram()
        ->context('write', array('$_content', '$_is_default ? false : ' . $escape));
    }
}