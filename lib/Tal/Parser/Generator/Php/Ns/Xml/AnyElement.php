<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Xml;

use DrSlump\Tal\Parser\Generator\Base;


class AnyElement extends Base\Ns\Element
{
    public function start()
    {
        $this->getWriter()
        ->xml(
            '<' .
            $this->name .
            $this->getAttributesString() .
            ($this->empty ? '/>' : '>')
        );
    }
    
    public function end()
    {
        if ( !$this->empty ) {
            $this->getWriter()
            ->xml( '</' . $this->name . '>' );
        }
    }
}