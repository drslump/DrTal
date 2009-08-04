<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;


class ContentAttribute extends Base\Ns\Attribute
{
    public function beforeElement()
    {
        // Makes sure the element is not empty
        $this->element->setEmpty(false);
    }
    
    public function beforeContent()
    {
        // Start capturing the element's content if any for default value
        $this->getWriter()->capture('$_tal_default');
    }
    
    public function afterContent()
    {
        $this->getWriter()->endCapture();
                
        // Get attribute's value
        $value = trim($this->value);
        
        // Check if we need to escape the result
        $escape = 'true';
        if (stripos($value, 'structure') === 0) {
            $escape = 'false';
            $value = substr( $value, strlen('structure') );
        }
        
        // Run tales expression to get value
        $this->doAlternates( $value, '$_tal_content', '$_tal_default' );
        
        // Finally echo the value
        $this->getWriter()
        ->context('write', array('$_tal_content', '$_tal_is_default ? false : ' . $escape));
    }
}