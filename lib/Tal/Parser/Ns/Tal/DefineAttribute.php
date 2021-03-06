<?php

namespace DrSlump\Tal\Parser\Ns\Tal;

use DrSlump\Tal\Parser;


class DefineAttribute extends Parser\Attribute
{
    /*
     
        TODO: Does not support 'default' keyword in paths or phptal's tal:define="mydef" 
    */
    public function beforeElement()
    {
        $value = trim($this->value);
        
        $this->getProgram()
        ->context('push');
        
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
            $value = trim( $this->doAlternates( $value, '$_define', '', true ) );
            
            // Check for a syntax error
            if ( !empty($value) && strpos($value, ';') !== 0 ) {
                throw new Parser\Exception('Synxtax error on tal:define expression');
            }
            
            // Remove the semi-colon to process a new define
            $value = substr($value, 1);
            
            // If the define is null we don't set it
            $this->getProgram()
            ->if('$_define !== NULL')
                //->code('$ctx->set(\'' . $defName . '\', $_tal_define, ' . ($global ? 'true' : 'false') . ');')
                ->context('set', array("'$defName'", '$_define', $global ? true : false))
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
        $this->getProgram()
        ->context('pop');
    }
}