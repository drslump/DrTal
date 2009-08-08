<?php

namespace DrSlump\Tal\Parser\Ns\Tal;

use DrSlump\Tal\Parser;


class OmitTagAttribute extends Parser\Attribute
{
    public function beforeElement()
    {
        if ( empty($this->value) ) {
            $this->getProgram()->capture();
        }
    }
    
    public function beforeContent()
    {
        if ( empty($this->value) ) {
            $this->getProgram()->endCapture();
        }
    }
    
    public function afterContent()
    {
        if ( empty($this->value) ) {
            $this->getProgram()->capture();
        }        
    }
    
    public function afterElement()
    {
        if ( empty($this->value) ) {
            $this->getProgram()->endCapture();
        }
    }
}