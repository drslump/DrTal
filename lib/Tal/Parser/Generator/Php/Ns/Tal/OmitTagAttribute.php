<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Attribute.php';

class OmitTagAttribute extends Base\Attribute
{
    public function beforeElement()
    {
        if ( empty($this->value) ) {
            $this->getCodegen()->capture();
        }
    }
    
    public function beforeContent()
    {
        if ( empty($this->value) ) {
            $this->getCodegen()->endCapture();
        }
    }
    
    public function afterContent()
    {
        if ( empty($this->value) ) {
            $this->getCodegen()->capture();
        }        
    }
    
    public function afterElement()
    {
        if ( empty($this->value) ) {
            $this->getCodegen()->endCapture();
        }
    }
}