<?php

namespace DrTal::Parser::Generator::Php::Ns::Metal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';

class UseMacroAttribute extends DrTal::Parser::Generator::Base::Attribute
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
            throw new DrTal_Parser_Exception( 'Macro name is missing' );
        } else if ( preg_match('/[^A-Za-z0-9_]/', $macroName) ) {
            throw new DrTal_Parser_Exception( 'Macro names must be only composed of alpha-numeric characters (A-Z and 0-9)' );
        }        

        if ($macroFile) {
            
            if ( !$this->loaded[$macroFile] ) {
                $tpl = DrTal::load( $macroFile );                
                $tpl->prepare();
                
                $this->loaded[$macroFile] = $tpl->getScriptIdent();
                
                $this->getCodegen()
                ->php( "@include_once '" . $tpl->getScriptStream() . "';" )->EOL();
            }
            
            $this->function = $this->loaded[$value];
            
        } else {
                
            $this->function = $this->getCodegen()->getTemplate()->getScriptIdent();
        }
        
        $this->function .= '_metal_macro_' . $macroName;
            
        $this->getCodegen()
        ->debugTales( 'use-macro', $this->value )
        
        // Check if the function exists before calling it
        ->if('!function_exists("' . $this->function . '")')
            ->php( 'throw new DrTal_Exception( "Macro \"' . $macroName . '\" not found" );' )->EOL()
        ->endIf()
        
        // Reset the metal slots array
        ->php('$_metal_slots = array();')->EOL()
        
        // Capture to skip the element start
        ->capture();
    }
    
    public function beforeContent()
    {
        $this->getCodegen()
        ->endCapture();        
    }
    
    public function afterContent()
    {
        $this->getCodegen()
        // Capture to skip the element end
        ->capture();        
    }
    
    public function afterElement()
    {
        $this->getCodegen()
        ->endCapture()        
        // Now we can call the macro function
        ->call( $this->function, array('$ctx', '$_metal_slots') );
    }

}
