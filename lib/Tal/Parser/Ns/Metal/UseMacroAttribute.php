<?php

namespace DrSlump\Tal\Parser\Ns\Metal;

use DrSlump\Tal\Parser;

class UseMacroAttribute extends Parser\Attribute
{
    static protected $loaded = array();
        
    public function beforeElement()
    {
        $value = trim($this->value);
        
        if ( $pos = strrpos( '/', $value ) ) {
            $macroFile = substr( $value, 0, $pos );
            $macroName = substr( $value, $pos+1 );
        } else {
            $macroFile = '';
            $macroName = $value;
        }
        
        if ( !$macroName ) {
            throw new Parser\Exception( 'Macro name is missing' );
        } else if ( preg_match('/[^A-Za-z0-9_]/', $macroName) ) {
            throw new Parser\Exception( 'Macro names must be only composed of alpha-numeric characters (A-Z and 0-9)' );
        }        

        if ($macroFile) {
            
            if ( !$this->loaded[$macroFile] ) {
                $tpl = Tal::load( $macroFile );                
                $tpl->prepare();
                
                $this->loaded[$macroFile] = $tpl->getScriptIdent();
                
                $this->getProgram()
                ->code( "@include_once '" . $tpl->getScriptStream() . "';" );
            }
            
            $this->function = $this->loaded[$value];
            
        } else {
                
            $this->function = $this->getParser()->getTemplate()->getScriptIdent();
        }
        
        $this->function .= '_metal_macro_' . $macroName;
            
        $this->getProgram()
        // Check if the function exists before calling it
        ->if('!function_exists("' . $this->function . '")')
            ->code( 'throw new DrSlump\\Tal\\Exception( "Macro \"' . $macroName . '\" not found" );' )
        ->endIf()        
        // Reset the metal slots array
        ->code('$_metal_slots = array();')        
        // Capture to skip the element start
        ->capture();
    }
    
    public function beforeContent()
    {
        $this->getProgram()
        ->endCapture();        
    }
    
    public function afterContent()
    {
        $this->getProgram()
        // Capture to skip the element end
        ->capture();
    }
    
    public function afterElement()
    {
        $this->getProgram()
        ->endCapture()        
        // Now we can call the macro function
        ->code( $this->function . '($ctx, $_metal_slots);' );
    }

}
