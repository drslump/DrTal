<?php

namespace DrTal::Parser::Generator::Php::Ns::Xml;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';

class AnyAttribute extends DrTal::Parser::Generator::Base::Attribute
{
    public function beforeElement()
    {
        $this->removed = false;
    }
}
