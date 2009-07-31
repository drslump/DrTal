<?php

namespace DrTal::Parser::Generator::Php::Ns::Tal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';


class ConditionAttribute extends DrTal::Parser::Generator::Base::Attribute
{
    public function beforeElement()
    {
        $value = trim( $this->doAlternates( $this->value, '$_tal_condition', '', true ) );
        
        if ( !empty($value) ) {
            throw new DrTal_Parser_Exception('Synxtax error on tal:condition expression');
        }
        
        $this->getCodegen()
            ->if('$_tal_condition');
    }
    
    public function afterElement()
    {
        $this->getCodegen()
            ->endIf();
    }
}