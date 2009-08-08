<?php

namespace DrSlump\Tal\Parser\Ns\Xml;

use DrSlump\Tal\Parser;


class AnyElement extends Parser\Element
{
    public function open()
    {
        $this->getProgram()
        ->xml('<' . $this->_name)
        ->appendList($this->getAttributesOpcodes())
        ->xml($this->isEmpty() ? '/>' : '>');
    }
    
    public function close()
    {
        if ( !$this->isEmpty() ) {
            $this->getProgram()
            ->xml( '</' . $this->_name . '>' );
        }
    }
}