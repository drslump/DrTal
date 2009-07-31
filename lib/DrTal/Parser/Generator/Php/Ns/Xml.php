<?php

namespace DrTal::Parser::Generator::Php::Ns;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Ns.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Xml/AnyElement.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Xml/AnyAttribute.php';

class Xml extends DrTal::Parser::Generator::Base::Ns
{
    public function __construct()
    {
        $this->registerElement( ::DrTal::ANY_ELEMENT, 'DrTal::Parser::Generator::Php::Ns::Xml::AnyElement' );
        $this->registerAttribute( ::DrTal::ANY_ATTRIBUTE, 'DrTal::Parser::Generator::Php::Ns::Xml::AnyAttribute' );
    }
    
    public function getNamespaceUri()
    {
        return ::DrTal::ANY_NAMESPACE;
    }
}