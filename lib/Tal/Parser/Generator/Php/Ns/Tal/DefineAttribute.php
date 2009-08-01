<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns/Attribute.php';

class DefineAttribute extends Base\Ns\Attribute
{
    /*
     
        TODO: Does not support 'default' keyword in paths or phptal's tal:define="mydef" 
    */
    public function beforeElement()
    {
        $value = trim($this->value);
        
        $this->getWriter()
        ->php('$ctx->push();')->EOL();
        
        while ( $value ) {
            
            $global = false;
            
            // get global o local
            if ( preg_match( '/^\s*(global|local)\s+/i', $value, $m ) ) {
                $global = strtolower($m[1]) === 'global';
                $value = substr($value, strlen($m[0]));
            }
            
            // get definition name
            if ( preg_match( '/^\s*([A-Za-z_][A-Za-z0-9_]*)\s+/', $value, $m ) ) {
                $value = substr( $value, strlen($m[0]) );
                
                $defName = $m[1];
                
            } else {
                
                throw new Parser\Exception('No definition name found');            
            }
            
            // Process expression
            $value = trim( $this->doAlternates( $value, '$_tal_define', '', true ) );
            
            // Check for a syntax error
            if ( !empty($value) && strpos($value, ';') !== 0 ) {
                throw new Parser\Exception('Synxtax error on tal:define expression');
            }
            
            // Remove the semi-colon to process a new define
            $value = substr($value, 1);
            
            // If the define is null we don't set it
            $this->getWriter()
            ->if('$_tal_define !== NULL')
                ->php('$ctx->set(\'' . $defName . '\', $_tal_define, ' . ($global ? 'true' : 'false') . ');')->EOL()
            ->endIf();
        }
    }
    
    public function beforeContent()
    {
        
    }
    
    public function afterContent()
    {
        
    }
    
    public function afterElement()
    {
        $this->getWriter()
        ->php('$ctx->pop();')->EOL();
    }
}