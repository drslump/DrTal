<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Metal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns/Attribute.php';

class DefineMacroAttribute extends Base\Ns\Attribute
{
    public function beforeElement()
    {
        $this->getWriter()
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
        
        $this->getWriter()
        ->endCapture()
        ->capture()
            ->EOL(true)
            ->comment( 'Macro: ' . $this->value )->EOL()
            ->php(  'function ' . $this->getWriter()->getTemplate()->getScriptIdent() .
                    '_metal_macro_' . $value . '($ctx, $_metal_slots) {')->EOL();
    }
    
    public function afterContent()
    {
        $this->getWriter()
            ->EOL()
            ->php('}')->EOL();
            
        $this->getWriter()->append( $this->getWriter()->getCapture() );
        
        $this->getWriter()
        ->endCapture()
        ->capture();
    }
    
    public function afterElement()
    {
        $this->getWriter()
        ->endCapture();
    }
}