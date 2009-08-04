<?php

namespace DrSlump\Tal\Parser\Generator\Php\Tales;

use DrSlump\Tal\Parser\Generator\Base;


class Php extends Base\Tales
{   

    // TODO: Make it work with ; as separator
    public function evaluate()
    {
        $exp = trim($this->_exp);
        if ( substr($exp, 0, 1) === "'" ) {
            
            $exp = substr($exp, 1, -1);
            $code = array();
            
            while ( preg_match('/[^\$]+|\$\$|\$\{([A-Za-z]+[^\}]+)\}/', $exp, $m) ) {
                
                $exp = substr($exp, strlen($m[0]));
                
                if ($m[0] === '$$') {
                    $code[] = "'\$'";
                } else if (strpos($m[0], '$') === 0) {
                    $code[] = $this->_parse($m[1]);
                } else {
                    $code[] = "'" . addslashes($m[0]) . "'";
                }
            }
            
            $this->_value = implode( ' . ', $code );
        
        } else {
            
            $this->_value = $this->_parse( $exp );            
        }
        
        $this->_exp = $exp;
    }
    
    protected function _parse()
    {
        $booleans = array(
            'NOT'   => '!',
            'OR'    => '||',
            'AND'   => '&&',
            'XOR'   => '^',
            'LT'    => '<',
            'LE'    => '<=',
            'GT'    => '>',
            'GE'    => '>=',
            'EQ'    => '==',
            'NE'    => '!='
        );
        
        $code = '';
        
        while ( preg_match('/((\]\.|\)\.|\:|\$)?[A-Za-z_][A-Za-z0-9_\.]*(\(|:)?)|\s\.\s|\.|\'[^\\\']*\'|"[^"]"|.+?/', $this->_exp, $m ) ) {
            
            $this->_exp = substr($this->_exp, strlen($m[0]));
            
            if ( isset($m[1]) && in_array($m[1], array_keys($booleans) ) ) {
                $code .= $booleans[$m[1]];
            } else if ( isset($m[2]) && strlen($m[2]) ) {
                $code .= str_replace( '.', '->', $m[1] );
            } else if ( isset($m[1]) && strlen($m[1]) ) {
                if ( ( strpos($m[1],'.')===false && !isset($m[3]) ) || strpos($m[1],'.') )
                    $code .= '$';
                    
                $code .= str_replace( '.', '->', $m[1] ); 
            } else {
                $code .= $m[0];
            }
        }
        
        return $code;
    }
    
}
