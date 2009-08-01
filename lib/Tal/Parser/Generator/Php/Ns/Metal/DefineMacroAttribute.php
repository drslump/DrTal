<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Metal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Attribute.php';

class DefineMacroAttribute extends Base\Attribute
{
    public function beforeElement()
    {
        $this->getCodegen()
        ->debugTales( 'define-macro', $this->value )
        ->capture();
    }
    
    public function beforeContent()
    {
        $value = trim($this->value);
        if ( !$value ) {
            throw new Parser\Exception( 'No name found for the macro definition' );
        } else if ( preg_match('/[^A-Za-z0-9_]/', $value) ) {
            throw new Parser\Exception( 'Macro names must be only composed of alpha-numeric characters (A-Z and 0-9)' );
        }
        
        $this->getCodegen()
        ->endCapture()
        ->capture()
            ->EOL(true)
            ->comment( 'Macro: ' . $this->value )->EOL()
            ->php(  'function ' . $this->getCodegen()->getTemplate()->getScriptIdent() .
                    '_metal_macro_' . $value . '($ctx, $_metal_slots) {')->EOL();
    }
    
    public function afterContent()
    {
        $this->getCodegen()
            ->EOL()
            ->php('}')->EOL();
            
        $this->getCodegen()->append( $this->getCodegen()->getCapture() );
        
        $this->getCodegen()
        ->endCapture()
        ->capture();
    }
    
    public function afterElement()
    {
        $this->getCodegen()
        ->endCapture();
    }
}