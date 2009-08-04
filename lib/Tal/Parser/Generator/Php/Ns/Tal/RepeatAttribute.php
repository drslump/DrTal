<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;


/*
    <li tal:repeat= item list ></li>
 
 */
class RepeatAttribute extends Base\Ns\Attribute
{
    static protected $counter = 0;
    protected $repeatName;
    protected $repeatVarName;
    
    public function beforeElement()
    {
        $value = $this->value;
        
        // get variable name
        if ( preg_match( '/^\s*([A-Za-z_][A-Za-z0-9_]*)\s+/', $value, $m ) ) {
            
            $value = substr( $value, strlen($m[0]) );
            $this->repeatName = $m[1];
            
        } else {
            throw new Parser\Exception('No repeat variable found');        
        }
        
        self::$counter++;

        // Evaluate the expression
        $value = trim( $this->doAlternates( $value, '$_tal_repeat_contents' ) );
        
        // Check for a syntax error
        if ( !empty($value) ) {
            throw new Parser\Exception('Synxtax error on tal:attributes expression');
        }
        
        $this->repeatVarName = '$_tal_repeat_' . self::$counter;
        
        // Initialize the repeat
        $this->getWriter()
        ->code($this->repeatVarName . ' = $ctx->initRepeat( \'' . $this->repeatName . '\', $_tal_repeat_contents );')
        ->context('push')
        ->iterate($this->repeatVarName . ' as ' . $this->repeatVarName . '_item')
            //->php('var_dump(' . $this->repeatVarName . '_item);')
            ->context('set', array("'$this->repeatName'", $this->repeatVarName . '_item'));
    }
    
    public function afterElement()
    {
        $this->getWriter()
        ->endIterate()
        ->code('unset(' . $this->repeatVarName . ');')
        ->context('pop')
        ->context('closeRepeat', array("'$this->repeatName'"));
    }
}