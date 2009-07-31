<?php

namespace DrTal::Parser::Generator::Php;

class Path extends DrTal::Parser::Generator::Base::Tales
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
            return '$ctx->path(\'' . implode('/', $parts) . '\')';
        } else {
            return '';
        }
    }
