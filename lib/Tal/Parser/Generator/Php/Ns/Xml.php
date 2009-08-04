<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal;


class Xml extends Base\Ns
{
    public function __construct()
    {
        $ns = __NAMESPACE__ . '\\Xml\\';
        $this->registerElement( Tal::ANY_ELEMENT, $ns . 'AnyElement' );
        $this->registerAttribute( Tal::ANY_ATTRIBUTE, $ns . 'AnyAttribute' );
    }
    
    public function getNamespaceUri()
    {
        return 'http://pollinimini.net/DrTal/NS/default';
    }
}