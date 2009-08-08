<?php

namespace DrSlump\Tal\Parser\Ns\Metal;

use DrSlump\Tal\Parser;

class DefineMacroAttribute extends Parser\Attribute
{
    protected $_ident = '';
    
    public function beforeElement()
    {
        $this->getWriter()
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
        
        $this->_ident = $this->getWriter()->getTemplate()->getScriptIdent() . '_metal_macro_' . $value;
        
        $this->getProgram()
        ->endCapture()
        ->append()
            ->comment('Macro: ' . $value)
            //->php(  'function ' . $this->getWriter()->getTemplate()->getScriptIdent() .
            //        '_metal_macro_' . $value . '($ctx, $_metal_slots) {');
            ->template($this->_ident);
    }
    
    public function afterContent()
    {
        $this->getProgram()
            ->endTemplate()
        ->endAppend()
        ->comment('Macro created in ' . $this->_ident );
            
        $this->getProgram()
        ->capture();
    }
    
    public function afterElement()
    {
        $this->getProgram()
        ->endCapture();
    }
}