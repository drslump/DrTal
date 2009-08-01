<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns/Attribute.php';

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
        ->php($this->repeatVarName . ' = $ctx->initRepeat( \'' . $this->repeatName . '\', $_tal_repeat_contents );')->EOL()
        ->php('$ctx->push();')
        ->foreach($this->repeatVarName . ' as ' . $this->repeatVarName . '_item')
            //->php('var_dump(' . $this->repeatVarName . '_item);')
            ->php('$ctx->set( \'' . $this->repeatName . '\', ' . $this->repeatVarName . '_item );');
    }
    
    public function afterElement()
    {
        $this->getWriter()
        ->endForeach()
        ->php('unset(' . $this->repeatVarName . ');')
        ->php('$ctx->pop();')
        ->php('$ctx->closeRepeat(\'' . $this->repeatName . '\');')->EOL();
    }
}