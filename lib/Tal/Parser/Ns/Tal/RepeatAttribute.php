<?php

namespace DrSlump\Tal\Parser\Ns\Tal;

use DrSlump\Tal\Parser;
use DrSlump\Tal;

/*
    <li tal:repeat= item list ></li>
 
 */
class RepeatAttribute extends Parser\Attribute
{
    static protected $counter = 0;
    protected $repeatName;
    protected $repeatVarName;
    
    public function beforeElement()
    {
        $value = ltrim($this->getValue());
        
        // get variable name
        if ( preg_match( '/^([A-Za-z_][A-Za-z0-9_]*)\s+/', $value, $m ) ) {
            
            $value = substr( $value, strlen($m[0]) );
            $this->repeatName = $m[1];
            
        } else {
            throw new Parser\Exception('No repeat variable found');        
        }
        
        self::$counter++;

        // Evaluate the expression
        $value = trim( $this->doAlternates( $value, '$_repeat' ) );
        
        // Check for a syntax error
        if ( !empty($value) ) {
            throw new Parser\Exception('Synxtax error on tal:attributes expression');
        }
        
        $this->repeatVarName = '$_repeat_' . self::$counter;
        
        // Initialize the repeat
        $this->getProgram()
        ->code($this->repeatVarName . ' = $ctx->initRepeat( \'' . $this->repeatName . '\', $_repeat );')
        ->context('push')
        ->iterate($this->repeatVarName, '$_item')
            ->context('set', array($this->repeatName, '$_item'));
    }
    
    public function afterElement()
    {
        $this->getProgram()
        ->endIterate()
        ->context('pop')
        ->context('closeRepeat', array($this->repeatName));        
    }
}