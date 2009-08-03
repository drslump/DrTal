<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Metal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns/Attribute.php';

class UseMacroAttribute extends Base\Ns\Attribute
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
                
                $this->getWriter()
                ->code( "@include_once '" . $tpl->getScriptStream() . "';" );
            }
            
            $this->function = $this->loaded[$value];
            
        } else {
                
            $this->function = $this->getWriter()->getTemplate()->getScriptIdent();
        }
        
        $this->function .= '_metal_macro_' . $macroName;
            
        $this->getWriter()
        ->debugTales( 'use-macro', $this->value )
        
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
        $this->getWriter()
        ->endCapture();        
    }
    
    public function afterContent()
    {
        $this->getWriter()
        // Capture to skip the element end
        ->capture();        
    }
    
    public function afterElement()
    {
        $this->getWriter()
        ->endCapture()        
        // Now we can call the macro function
        ->call( $this->function, array('$ctx', '$_metal_slots') );
    }

}
