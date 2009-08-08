<?php

namespace DrSlump\Tal\Parser\Ns\Tal;

use DrSlump\Tal\Parser;


class OnErrorAttribute extends Parser\Attribute
{
    static $counter = 0;
    protected $echoFunc;
    protected $varName;
    
    public function beforeElement()
    {
    }
    
    public function beforeContent()
    {
        $value = $this->value;
        
        // Check if we need to escape the output
        $this->echoFunc = 'echo $ctx->escape';
        if ( preg_match('/^\s*(text|structure)\s+/i', $value, $m) ) {
            if ( strtolower($m[1]) === 'structure' )
                $this->echoFunc = 'print';
                
            $value = substr( $value, strlen($m[0]) );
        }
        
        $this->varName = '$_onError_' . self::$counter;
        self::$counter++;
        
        $value = trim( $this->doAlternates( $value,  $this->varName ) );
        
        $this->getProgram()
        ->try()
        ->capture('$onerror');
    }
    
    public function afterContent()
    {
        $this->getProgram()
        ->endCapture()
        ->var('$onerror')
        ->catch('Exception')
            ->code($this->echoFunc . '(' . $this->varName . ');')
        ->endTry();        
    }
    
    public function afterElement()
    {
    }
}