<?php

namespace DrSlump\Tal\Parser\Generator\Php\Tales;

use DrSlump\Tal\Parser\Generator\Base;


class String extends Base\Tales
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
                $value[] = '$ctx->path(\'' . $m[1] . '\')';
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
            if ( strpos($v, '$ctx->')===0 ) {
                if (strlen($prev)) {
                    $parts[] = "'" . addcslashes($prev, '\'\\') . "'";
                    $prev = '';
                }
                $parts[] = $v;
            } else {
                $prev .= $v;
            }
        }
        if (strlen($prev)) {
            $parts[] = "'" . addcslashes($prev, '\'\\') . "'";
        }
        
        $this->_value = implode( ' . ', $parts );
    }    
}