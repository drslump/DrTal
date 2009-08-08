<?php

namespace DrSlump\Tal\Parser\Ns\Tal;

use DrSlump\Tal\Parser;


class ConditionAttribute extends Parser\Attribute
{
    public function beforeElement()
    {
        $this->doAlternates( $this->getValue(), '$_condition', '', true );
        
        $this->getProgram()
            ->if('$_condition');
    }
    
    public function afterElement()
    {
        $this->getProgram()
            ->endIf();
    }
}