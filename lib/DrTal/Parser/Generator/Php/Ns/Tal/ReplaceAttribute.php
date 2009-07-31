<?php

namespace DrTal::Parser::Generator::Php::Ns::Tal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';


class ReplaceAttribute extends DrTal::Parser::Generator::Base::Attribute
{
    public function beforeElement()
    {
        // Call tal:content's beforeContent() handler
        parent::beforeContent();
    }
    
    public function beforeContent()
    {
        // just defined to skip the original ContentAttribute behaviour
    }
    
    public function afterContent()
    {
        // just defined to skip the original ContentAttribute behaviour
    }

    public function afterElement()
    {
        // Call tal:content's afterContent() handler
        parent::afterContent();
    }
}