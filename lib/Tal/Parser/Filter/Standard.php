<?php

namespace DrSlump\Tal\Parser\Filter;

use DrSlump\Tal\Parser\Filter;
use DrSlump\Tal\Parser\OpcodeList;

class Standard extends Filter
{

    public function text( $opcodes )
    {
        $result = new OpcodeList();
        foreach($opcodes as $op) {
            if ($op->getName() === 'XML') {
                // Interpolate
                $regexp = '/(\$\{[^\}]+\})/';
                $chunks = preg_split($regexp, $op->arg(0), null, PREG_SPLIT_DELIM_CAPTURE);
                foreach($chunks as $chunk) {
                    if (preg_match($regexp, $chunk)) {
                        $result->path(substr($chunk, 2, -1));
                    } else {
                        $result->xml($chunk);
                    }
                }
            } else {
                $result->push($op);
            }
        }
        
        return $result;
    }
    
    public function cdata( $opcodes )
    {
        // Reuse the text filter
        return $this->text($opcodes);
    }
    
    public function comment( $opcodes )
    {
        // Do not trim if it's more than just text
        if (count($opcodes) != 1) return $opcodes;
        
        $opcodes[0]->setArg(0, trim($opcodes[0]->arg(0)));
        
        return $opcodes;
    }
}