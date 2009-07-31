<?php

namespace DrTal::Parser::Generator::Php::Ns::Metal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';

class DefineSlotAttribute extends DrTal::Parser::Generator::Base::Attribute
{

    public function beforeElement()
    {
        $this->getCodegen()
        ->debugTales( 'define-slot', $this->value )
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
        $this->getCodegen()
        ->else()
            ->php('echo $_metal_slots[\'' . trim($this->value) . '\'];')
        ->endIf();
    }

}