<?php

namespace DrTal::Parser::Generator::Php::Ns::Tal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';

class ContentAttribute extends DrTal::Parser::Generator::Base::Attribute
{
    public function beforeElement()
    {
        // Makes sure the element is not empty
        $this->element->setEmpty(false);
    }
    
    public function beforeContent()
    {
        // Start capturing the element's content if any
        $this->getCodegen()
        ->closePhp()  // make sure PHP mode is closed
        ->capture();
    }
    
    public function afterContent()
    {
        // Store the element's contents
        $default = $this->getCodegen()->getCapture(true);
        
        // Stop capturing since we already have the contents
        $this->getCodegen()
        ->endCapture();
        
        
        $value = trim($this->value);
        
        // Check if we need to escape the output
        if ( stripos( $value, 'structure ' ) === 0 ) {
            $echoFunc = 'print';
            $value = substr( $value, strlen('structure ') );
        } else {
            $echoFunc = 'echo $ctx->escape';
        }
        
        
        $value = trim( $this->doAlternates( $value,  '$_tal_content', $default ) );
        
        // Finally echo the value
        $this->getCodegen()
            ->php( $echoFunc . '($_tal_content);' );        
    }
}