<?php

namespace DrTal::Parser::Generator::Php::Ns;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Ns.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Tal/BlockElement.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Tal/AttributesAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Tal/CommentAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Tal/ConditionAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Tal/ContentAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Tal/DefineAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Tal/OmitTagAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Tal/OnErrorAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Tal/RepeatAttribute.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Php/Ns/Tal/ReplaceAttribute.php';

class Tal extends DrTal::Parser::Generator::Base::Ns {
    
    public function __construct()
    {
        $ns = 'DrTal::Parser::Generator::Php::Ns::Tal::';
        
        $this->registerElement( 'block', $ns . 'BlockElement' );
        $this->registerAttribute( 'on-error', $ns . 'OnErrorAttribute', ::DrTal::PRIORITY_MAXIMUM + 1 );
        $this->registerAttribute( 'define', $ns . 'DefineAttribute', ::DrTal::PRIORITY_MAXIMUM );
        $this->registerAttribute( 'condition', $ns . 'ConditionAttribute', ::DrTal::PRIORITY_VERYHIGH );
        $this->registerAttribute( 'repeat', $ns . 'RepeatAttribute', ::DrTal::PRIORITY_HIGH );
        $this->registerAttribute( 'replace', $ns . 'ReplaceAttribute', ::DrTal::PRIORITY_MEDIUM );
        $this->registerAttribute( 'content', $ns . 'ContentAttribute', ::DrTal::PRIORITY_MEDIUM );
        $this->registerAttribute( 'attributes', $ns . 'AttributesAttribute', ::DrTal::PRIORITY_LOW );
        $this->registerAttribute( 'comment', $ns . 'CommentAttribute', ::DrTal::PRIORITY_VERYLOW ); 
        $this->registerAttribute( 'omit-tag', $ns . 'OmitTagAttribute', ::DrTal::PRIORITY_VERYLOW );
    }
    
    public function getNamespaceUrl()
    {
        return 'http://xml.zope.org/namespaces/tal';
    }

}