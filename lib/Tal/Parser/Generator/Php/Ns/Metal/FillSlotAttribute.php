<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Metal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

class FillSlotAttribute extends Base\Ns\Attribute
{
    public function beforeElement()
    {
        $this->getWriter()
        ->debugTales( 'use-slot', $this->value )
        ->capture('$_metal_slots[\'' . trim($this->value) . '\']');
    }
    
    public function beforeContent()
    {
    }
    
    public function afterContent()
    {
    }

    public function afterElement()
    {
        $this->getWriter()
        ->endCapture();
    }
}