<?php

namespace DrSlump\Tal\Parser\Generator\Php;

class Tales {
    
    static public function path( $codegen, &$exp, $isPrefix )
    {
        $parts = array();
        $exp = trim($exp);
        
        if ( preg_match( '/^[A-Za-z][A-Za-z0-9_]*/', $exp, $m ) ) {
            
            $parts[] = $m[0];
            $exp = substr( $exp, strlen($m[0]) );
            
            while ( preg_match('/^\s*\/([A-Za-z0-9_][A-Za-z0-9_ \.~,-]*)/', $exp, $m ) ) {
                
                $exp = substr($exp, strlen($m[0]));
                
                if ( strlen($m[1]) ) {                    
                    $parts[] = $m[1];                    
                }
            }
        }
        
        $isPrefix = false;
        
        if ( !empty($parts) ) {
            return implode('/', $parts);
        } else {
            return '';
        }
    }
    
    static public function string( $codegen, &$exp )
    {
        $quoteDelim = false;
        if ( strpos($exp, "'") === 0 ) {
            $quoteDelim = true;
            $exp = substr($exp, 1);
        }
        
        $value = array();
        while ( preg_match('/[^;\$]+|\'\'|\'|;;|;|\$\$|\$\{?([A-Za-z]+[A-Za-z0-9_\/-]*)\}?/', $exp, $m) ) {
            
            if ( $m[0] === '\'\'' ) {
                if ( $quoteDelim ) {
                    $value[] = "'";
                } else {
                    $value[] = "''";
                }
            } else if ( $m[0] === '\'' ) {
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
        
        // enclose in single quotes the strings
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
        
        $isPrefix = false;
        return implode( ' . ', $parts );
    }
    
    static public function not( $codegen, &$exp, &$isPrefix )
    {
        $isPrefix = true;
        return '!';
    }
    
    static public function nocall( $codegen, &$exp )
    {
        // This will not work, we might need a $ctx->nocall which returns a dummy object
        $return = '\'nocall/' . trim($exp) . '\'';
        $exp = '';
        return $return;
    }

    static public function exists( $codegen, &$exp )
    {    
        $return = '$ctx->exists(\'' . trim($exp) . '\')';
        $exp = '';
        return $return;
    }

    // TODO: Make it work with ; as separator    
    static public function php( $codegen, &$exp )
    {
        $exp = trim($exp);
        if ( substr($exp, 0, 1) === "'" ) {
            
            $exp = substr($exp, 1, -1);
            $code = array();
            
            while ( preg_match('/[^\$]+|\$\$|\$\{([A-Za-z]+[^\}]+)\}/', $exp, $m) ) {
                
                $exp = substr($exp, strlen($m[0]));
                
                if ($m[0] === '$$') {
                    $code[] = "'\$'";
                } else if (strpos($m[0], '$') === 0) {
                    $code[] = self::php_parse($m[1]);
                } else {
                    $code[] = "'" . addslashes($m[0]) . "'";
                }
            }
            
            $code = implode( ' . ', $this->code );
        
        } else {
            
            $code = self::php_parse( $exp );
            
        }
        
        return $code;
    }
    
    static protected function php_parse(&$exp)
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
        
        while ( preg_match('/((\]\.|\)\.|\:|\$)?[A-Za-z_][A-Za-z0-9_\.]*(\(|:)?)|\s\.\s|\.|\'[^\\\']*\'|"[^"]"|.+?/', $exp, $m ) ) {
            
            $exp = substr($exp, strlen($m[0]));
            
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
