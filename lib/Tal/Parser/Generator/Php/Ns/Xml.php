<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Xml/AnyElement.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Xml/AnyAttribute.php';

class Xml extends Base\Ns
{
    public function __construct()
    {
        $ns = 'DrSlump\\Tal\\Parser\\Generator\\Php\\Ns\\Xml\\';
        $this->registerElement( Tal::ANY_ELEMENT, $ns . 'AnyElement' );
        $this->registerAttribute( Tal::ANY_ATTRIBUTE, $ns . 'AnyAttribute' );
    }
    
    public function getNamespaceUri()
    {
        return 'http://pollinimini.net/DrTal/NS/default';
    }
}