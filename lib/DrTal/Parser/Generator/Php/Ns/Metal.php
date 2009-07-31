<?php

namespace DrTal::Parser::Generator::Php::Ns;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Ns.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Metal/DefineMacroAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Metal/DefineSlotAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Metal/UseMacroAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Metal/FillSlotAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Metal/ExtendMacroAttribute.php';

class Metal extends DrTal::Parser::Generator::Base::Ns {
    
    public function __construct()
    {
        $ns = 'DrTal::Parser::Generator::Base::Ns::Metal::';
        
        $this->registerAttribute( 'define-macro', $ns . 'DefineMacroAttribute' );
        $this->registerAttribute( 'define-slot', $ns . 'DefineSlotAttribute' );
        $this->registerAttribute( 'use-macro', $ns . 'UseMacroAttribute', ::DrTal::PRIORITY_MINIMUM );
        $this->registerAttribute( 'fill-slot', $ns . 'FillSlotAttribute' );
        $this->registerAttribute( 'extend-macro', $ns . 'ExtendMacroAttribute' );
    }
    
    public function getNamespaceUrl()
    {
        return 'http://xml.zope.org/namespaces/metal';
    }

}