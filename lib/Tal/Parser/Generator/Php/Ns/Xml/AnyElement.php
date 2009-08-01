<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Xml;

use DrSlump\Tal\Parser\Generator\Base;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Element.php';

class AnyElement extends Base\Element
{
    public function start()
    {
        $this->getCodegen()
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
            $this->getCodegen()
            ->xml( '</' . $this->name . '>' );
        }
    }
}