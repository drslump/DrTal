<?php

namespace DrSlump\Tal\Parser\Ns;

use DrSlump\Tal\Parser;
use DrSlump\Tal;


class Xml extends Parser\Ns
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