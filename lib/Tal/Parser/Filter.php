<?php

namespace DrSlump\Tal\Parser;

abstract class Filter {
    
    public function whitespace( $opcodes )
    {
        return $opcodes;
    }

    public function text( $opcodes )
    {
        return $opcodes;
    }
    
    public function cdata( $opcodes )
    {
        return $opcodes;
    }
    
    public function comment( $opcodes )
    {
        return $opcodes;
    }
    
    public function pi( $name, $opcodes )
    {
        return $opcodes;
    }
}