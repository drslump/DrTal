<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Attribute.php';


class ConditionAttribute extends Base\Attribute
{
    public function beforeElement()
    {
        $value = trim( $this->doAlternates( $this->value, '$_tal_condition', '', true ) );
        
        if ( !empty($value) ) {
            throw new Parser\Exception('Synxtax error on tal:condition expression');
        }
        
        $this->getCodegen()
            ->if('$_tal_condition');
    }
    
    public function afterElement()
    {
        $this->getCodegen()
            ->endIf();
    }
}