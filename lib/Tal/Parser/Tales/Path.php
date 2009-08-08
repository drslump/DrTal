<?php

namespace DrSlump\Tal\Parser\Tales;

use DrSlump\Tal\Parser;


class Path extends Parser\Tales
{
    public function evaluate()
    {
        $parts = array();
        $exp = trim($this->_exp);
        
        if ( preg_match( '/[A-Za-z][A-Za-z0-9_]*/', $exp, $m ) ) {
            
            $parts[] = $m[0];
            $exp = substr( $exp, strlen($m[0]) );
            
            while ( preg_match( '/^\s*\/([A-Za-z0-9_][A-Za-z0-9_\.~,-]*)/', $exp, $m ) ) {
                
                $exp = substr( $exp, strlen($m[0]) );
                if (strlen($m[1])) {
                    $parts[] = $m[1];
                }                
            }
        }
        
        $this->_exp = $exp;
        
        if ( !empty($parts) ) {
            $path = implode('/', $parts);
            $this->_opcodes->context('path', array($path, false));
        }
    }
}
