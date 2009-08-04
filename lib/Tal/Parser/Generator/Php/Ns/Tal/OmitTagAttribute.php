<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;


class OmitTagAttribute extends Base\Ns\Attribute
{
    public function beforeElement()
    {
        if ( empty($this->value) ) {
            $this->getWriter()->capture();
        }
    }
    
    public function beforeContent()
    {
        if ( empty($this->value) ) {
            $this->getWriter()->endCapture();
        }
    }
    
    public function afterContent()
    {
        if ( empty($this->value) ) {
            $this->getWriter()->capture();
        }        
    }
    
    public function afterElement()
    {
        if ( empty($this->value) ) {
            $this->getWriter()->endCapture();
        }
    }
}