<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns;

use DrSlump\Tal\Parser\Generator\Base;

class Metal extends Base\Ns {
    
    public function __construct()
    {
        $ns = 'DrTal\\Parser\\Generator\\Base\\Ns\\Metal\\';
        
        $this->registerAttribute( 'define-macro', $ns . 'DefineMacroAttribute' );
        $this->registerAttribute( 'define-slot', $ns . 'DefineSlotAttribute' );
        $this->registerAttribute( 'use-macro', $ns . 'UseMacroAttribute', \DrSlump\Tal::PRIORITY_MINIMUM );
        $this->registerAttribute( 'fill-slot', $ns . 'FillSlotAttribute' );
        $this->registerAttribute( 'extend-macro', $ns . 'ExtendMacroAttribute' );
    }
    
    public function getNamespaceUri()
    {
        return 'http://xml.zope.org/namespaces/metal';
    }

}