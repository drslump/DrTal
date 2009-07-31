<?php

namespace DrTal::Parser::Generator::Php::Ns::Xml;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Element.php';

class AnyElement extends DrTal::Parser::Generator::Base::Element
{
    public function start()
    {
        $this->getCodegen()
        ->xml(
            '<' .
            $this->name .
            $this->getAttributesString() .
            ($this->empty ? '/>' : '>')
        );
    }
    
    public function end()
    {
        if ( !$this->empty ) {
            $this->getCodegen()
            ->xml( '</' . $this->name . '>' );
        }
    }
}