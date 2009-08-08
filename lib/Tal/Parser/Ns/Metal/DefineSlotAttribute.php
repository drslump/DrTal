<?php

namespace DrSlump\Tal\Parser\Ns\Metal;

use DrSlump\Tal\Parser;

class DefineSlotAttribute extends Parser\Attribute
{

    public function beforeElement()
    {
        $this->getProgram()
        ->if('!isset($_metal_slots[\'' . trim($this->value) . '\'])');
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
        ->else()
            ->code('echo $_metal_slots[\'' . trim($this->value) . '\'];')
        ->endIf();
    }

}