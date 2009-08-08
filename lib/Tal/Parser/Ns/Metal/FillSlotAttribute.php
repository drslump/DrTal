<?php

namespace DrSlump\Tal\Parser\Ns\Metal;

use DrSlump\Tal\Parser;

class FillSlotAttribute extends Parser\Attribute
{
    public function beforeElement()
    {
        $this->getProgram()
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
        $this->getProgram()
        ->endCapture();
    }
}