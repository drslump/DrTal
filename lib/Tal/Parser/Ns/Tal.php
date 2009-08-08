<?php

namespace DrSlump\Tal\Parser\Ns;

use DrSlump\Tal as DrTal;
use DrSlump\Tal\Parser;

class Tal extends Parser\Ns {
    
    public function __construct()
    {
        $ns = __NAMESPACE__ . '\\Tal\\';
        
        $this->registerElement( 'block', $ns . 'BlockElement' );
        $this->registerAttribute( 'on-error', $ns . 'OnErrorAttribute', DrTal::PRIORITY_HIGHEST );
        $this->registerAttribute( 'define', $ns . 'DefineAttribute', DrTal::PRIORITY_HIGHER );
        $this->registerAttribute( 'condition', $ns . 'ConditionAttribute', DrTal::PRIORITY_HIGH );
        $this->registerAttribute( 'repeat', $ns . 'RepeatAttribute', DrTal::PRIORITY_NORMAL );
        $this->registerAttribute( 'replace', $ns . 'ReplaceAttribute', DrTal::PRIORITY_LOW );
        $this->registerAttribute( 'content', $ns . 'ContentAttribute', DrTal::PRIORITY_LOW );
        $this->registerAttribute( 'attributes', $ns . 'AttributesAttribute', DrTal::PRIORITY_LOWER );
        $this->registerAttribute( 'comment', $ns . 'CommentAttribute', DrTal::PRIORITY_LOWEST ); 
        $this->registerAttribute( 'omit-tag', $ns . 'OmitTagAttribute', DrTal::PRIORITY_LOWEST );

    }
    
    public function getNamespaceUri()
    {
        return 'http://xml.zope.org/namespaces/tal';
    }

}