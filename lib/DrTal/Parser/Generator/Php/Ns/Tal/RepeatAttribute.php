<?php

namespace DrTal::Parser::Generator::Php::Ns::Tal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';

class RepeatAttribute extends DrTal::Parser::Generator::Base::Attribute
{
    static protected $counter = 0;
    protected $repeatName;
    protected $repeatVarName;
    
    public function beforeElement()
    {
        // get attribute name
        if ( preg_match( '/^\s*([A-Za-z_][A-Za-z0-9_]*)\s+/', $this->value, $m ) ) {
            
            $value = substr( $this->value, strlen($m[0]) );
            $this->repeatName = $m[1];
            
        } else {            
            throw new DrTal_Parser_Exception('No repeat variable found');        
        }
        
        self::$counter++;
        
        // Evaluate the expression
        $value = trim( $this->doAlternates( $value, '$_tal_repeat_contents' ) );
        
        // Check for a syntax error
        if ( !empty($value) ) {
            throw new DrTal_Parser_Exception('Synxtax error on tal:attributes expression');
        }
        
        $this->repeatVarName = '$_tal_repeat_' . self::$counter;
        
        // Initialize the repeat
        $this->getCodegen()
        ->php($this->repeatVarName . ' = $ctx->initRepeat( \'' . $this->repeatName . '\', $_tal_repeat_contents );')->EOL()
        ->php('$ctx->push();')
        ->foreach($this->repeatVarName . ' as ' . $this->repeatVarName . '_item')
            //->php('var_dump(' . $this->repeatVarName . '_item);')
            ->php('$ctx->set( \'' . $this->repeatName . '\', ' . $this->repeatVarName . '_item );');
    }
    
    public function afterElement()
    {
        $this->getCodegen()
        ->endForeach()
        ->php('unset(' . $this->repeatVarName . ');')
        ->php('$ctx->pop();')
        ->php('$ctx->closeRepeat(\'' . $this->repeatName . '\');')->EOL();
    }
}