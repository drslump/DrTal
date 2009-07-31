<?php

namespace DrTal::Parser::Generator::Php::Ns::Tal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';

class OnErrorAttribute extends DrTal::Parser::Generator::Base::Attribute
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
        
        $this->varName = '$_tal_onError_' . self::$counter;
        self::$counter++;
        
        $value = trim( $this->doAlternates( $value,  $this->varName ) );
        
        $this->getCodegen()
        ->try()
        ->php('ob_start();');        
    }
    
    public function afterContent()
    {
        $this->getCodegen()
        ->php('ob_end_flush();')
        ->catch('Exception')
            ->php('ob_end_clean();')
            ->php($this->echoFunc . '(' . $this->varName . ');')
        ->endTry();        
    }
    
    public function afterElement()
    {
    }
}