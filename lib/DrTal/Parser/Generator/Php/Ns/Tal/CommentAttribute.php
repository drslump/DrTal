<?php

namespace DrTal::Parser::Generator::Php::Ns::Tal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';


class CommentAttribute extends DrTal::Parser::Generator::Base::Attribute
{
    public function beforeElement()
    {
        $this->getCodegen()->comment( $value );
    }
}