<?php

namespace DrSlump\Tal\Parser\Ns;

use DrSlump\Tal\Parser;

class Metal extends Parser\Ns {
    
    public function __construct()
    {
        $ns = __NAMESPACE__ . '\\Metal\\';
        
        $this->registerAttribute( 'define-macro', $ns . 'DefineMacroAttribute' );
        $this->registerAttribute( 'define-slot', $ns . 'DefineSlotAttribute' );
        $this->registerAttribute( 'use-macro', $ns . 'UseMacroAttribute', \DrSlump\Tal::PRIORITY_LOWEST );
        $this->registerAttribute( 'fill-slot', $ns . 'FillSlotAttribute' );
        $this->registerAttribute( 'extend-macro', $ns . 'ExtendMacroAttribute' );
    }
    
    public function getNamespaceUri()
    {
        return 'http://xml.zope.org/namespaces/metal';
    }

}