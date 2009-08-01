<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns;

use DrSlump\Tal\Parser\Generator\Base;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Metal/DefineMacroAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Metal/DefineSlotAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Metal/UseMacroAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Metal/FillSlotAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Metal/ExtendMacroAttribute.php';

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
    
    public function getNamespaceUrl()
    {
        return 'http://xml.zope.org/namespaces/metal';
    }

}