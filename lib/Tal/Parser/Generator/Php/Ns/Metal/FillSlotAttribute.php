<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Metal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns/Attribute.php';

class FillSlotAttribute extends Base\Ns\Attribute
{
    public function beforeElement()
    {
        $this->getWriter()
        ->debugTales( 'use-slot', $this->value )
        ->capture('$_metal_slots[\'' . trim($this->value) . '\']');
    }
    
    public function beforeContent()
    {
    }
    
    public function afterContent()
    {
    }

    public function afterElement()
    {
        $this->getWriter()
        ->endCapture();
    }
}