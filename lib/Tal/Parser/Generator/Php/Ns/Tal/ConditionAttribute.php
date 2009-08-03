<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns/Attribute.php';


class ConditionAttribute extends Base\Ns\Attribute
{
    public function beforeElement()
    {
        $this->doAlternates( $this->value, '$_tal_condition', '', true );
        if ( !empty($value) ) {
            throw new Parser\Exception('Synxtax error on tal:condition expression');
        }
        
        $this->getWriter()
            ->if('$_tal_condition');
    }
    
    public function afterElement()
    {
        $this->getWriter()
            ->endIf();
    }
}