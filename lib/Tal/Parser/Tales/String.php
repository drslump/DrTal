<?php

namespace DrSlump\Tal\Parser\Tales;

use DrSlump\Tal\Parser;
use DrSlump\Tal\Parser\OpcodeList;

class String extends Parser\Tales
{
    
    public function evaluate()
    {
        $exp = trim($this->_exp);
        $quoteDelim = false;
        if ( strpos($exp, "'") === 0 ) {
            $quoteDelim = true;
            $exp = substr($exp, 1);
        }
        
        $value = array();
        while ( preg_match('/[^;\$\']+|\'\'|\'|;;|;|\$\$|\$\{?([A-Za-z]+[A-Za-z0-9_\/-]*)\}?/', $exp, $m) ) {
            if ( $m[0] === "''" ) {
                if ( $quoteDelim ) {
                    $value[] = "'";
                } else {
                    $value[] = "''";
                }
            } else if ( $m[0] === "'" ) {
                if ( $quoteDelim ) {
                    $exp = substr($exp, 1);
                    break;
                } else {
                    $value = "'";
                }
            } else if ( $m[0] === ';;' ) {
                $value[] = ';';
            } else if ($m[0] === ';') {
                break;
            } else if ($m[0] === '$$') {
                $value[] = '$';
            } else if (strpos($m[0], '$') === 0) {
                //$value[] = '$ctx->path(\'' . $m[1] . '\')';
                $value[] = OpcodeList::factory('context', 'path', array($m[1]) );
            } else {
                $value[] = $m[0];
            }
            
            $exp = substr($exp, strlen($m[0]));            
        }

        // Store the reduced expression
        $this->_exp = $exp;
        
        // enclose in single quotes the strings concatenating neighboor strings
        $parts = array();
        $prev = '';
        foreach ( $value as $v ) {
            if ( $v instanceof OpcodeList ) { //strpos($v, '$ctx->')===0 ) {
                if (strlen($prev)) {
                    //$parts[] = "'" . addcslashes($prev, '\'\\') . "'";
                    $this->_opcodes->code("'" . addcslashes($prev, "'\\") . "' . ");
                    $prev = '';
                }
                //$parts[] = $v;
                $this->_opcodes->appendList($v);
                $this->_opcodes->code(' . ');
            } else {
                $prev .= $v;
            }
        }
        if (strlen($prev)) {
            //$parts[] = "'" . addcslashes($prev, '\'\\') . "'";
            $this->_opcodes->code("'" . addcslashes($prev, "'\\") . "'");
        } else {
            // Add an empty string since we have a concatenation operator '.' before
            $this->_opcodes->code("''");
        }
        
        //$this->_value = implode( ' . ', $parts );
    }    
}