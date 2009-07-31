<?php

namespace DrTal::Parser::Generator::Php::Ns::Metal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';

class FillSlotAttribute extends DrTal::Parser::Generator::Base::Attribute
{
    public function beforeElement()
    {
        $this->getCodegen()
        ->debugTales( 'use-slot', $this->value )
        ->php('ob_start();')->EOL();
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
        ->php('$_metal_slots[\'' . trim($this->value) . '\'] = ob_get_contents();')->EOL()
        ->php('ob_end_clean();')->EOL();
    }
}