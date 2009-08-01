<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Metal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Attribute.php';

class FillSlotAttribute extends Base\Attribute
{
    public function beforeElement()
    {
        $this->getCodegen()
        ->debugTales( 'use-slot', $this->value )
        ->php('ob_start();')->EOL();
    }
    
    public function beforeContent()
    {
    }
    
    public function afterContent()
    {
    }

    public function afterElement()
    {
        $this->getCodegen()
        ->php('$_metal_slots[\'' . trim($this->value) . '\'] = ob_get_contents();')->EOL()
        ->php('ob_end_clean();')->EOL();
    }
}