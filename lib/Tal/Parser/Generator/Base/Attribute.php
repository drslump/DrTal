<?php

namespace DrSlump\Tal\Parser\Generator\Base;

abstract class Attribute
{
    protected $element;
    protected $name;
    protected $value;
    protected $escape;
    protected $removed;
    
    public function __construct( Element $element, $name, $value, $escape = true )
    {
        $this->element = $element;
        $this->name = $name;
        $this->value = $value;
        $this->escape = $escape;
        $this->removed = true;
    }
    
    public function getName()
    {
        if ( $pos = strpos($this->name, ':') ) {
            return substr( $this->name, 0, $pos );
        } else {
            return $this->name;
        }
    }
    
    public function getPrefix()
    {
        return substr( $this->name, 0, (int)strpos($this->name, ':') );
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function getRemoved()
    {
        return $this->removed;
    }
    
    public function getEscape()
    {
        return $this->escape;
    }
    
    protected function getCodegen()
    {
        return $this->element->getCodegen();
    }
    
    /*
     Method: tales
        Evaluates a tales expression
        
     Arguments:
        &$exp       - the tales expression to evaluate 
     
     Returns:
     
    */
    protected function tales( &$exp, &$isPrefix )
    {
        $exp = ltrim( $exp );
        if ( strpos($exp, "'")===0 ) {
            $modifier = 'string';
        } else if ( strpos($exp, '!' )===0) {
            $modifier = 'not';
            $exp = substr( $exp, 1 );
        } else if ( preg_match( '/^([A-Za-z][A-Za-z_-]*[A-Za-z_]*):/', $exp, $m ) ) {
            $modifier = $m[1];
            $exp = substr( $exp, strlen($m[0]) );
        } else {
            $modifier = 'path';
        }
        
        $callable = $this->element->getParser()->getTales( $modifier );
        if ( !$callable ) {
            throw new Parser\Exception('Tales handler "' . $modifier . '" was not found');
        }
        
        //return call_user_func( $callable, $this->getCodegen(), &$exp, &$isPrefix );        return call_user_func( $callable, $this->getCodegen(), $exp, $isPrefix );

    }
    
    /*
        The expression can have alternates ('|') so we need to build a runtime
        expression which checks the alternatives in the given order and gets the
        first valid one or exits with an exception if no alternative is valid.
        It generates something like this (for "my/path | my/alternative/path"):
        
        try {
            if (!$varName = $ctx->path('my/path'))
                throw new DrTal_Exception();
        } catch (DrTal_Exception $e) {
            try {
                if (!$varName = 'a string value from string:....'))
                    throw new DrTal_Exception();
            } catch (DrTal_Exception $e) {                
                // if the are no more alternatives check if we actually have a value
                if ($varName === null)
                    throw $e;                    
            }
        }
    */
    protected function doAlternates( $expression, $varName, $default = '', $allowNull = false )
    {
        // Initialize the variable
        $this->getCodegen()
        ->php($varName . ' = NULL;')->EOL();
        
        $nested = 0;
        $prevExpression = $expression;
        $value = '';
        while ( $v = $this->tales($expression, $isPrefix) ) {
            
            $value .= $v;
            
            if ( $isPrefix ) {
                continue;
            }
            
            // Detect the default keyword and if pressent overwrite the value
            if ( preg_match('/^\s*default\b/i', $prevExpression) ) {
                $value = "'" . addcslashes($default, '\'\\') . "'";
            }
            
            $this->getCodegen()
            ->try()
                ->if('!(' . $varName . ' = ' . $value . ')')
                    ->throw('DrTal_Exception')
                ->endIf()
            ->catch('DrTal_Exception');
            
            $nested++;
            
            $expression = trim($expression);
            
            // An alternate is following
            if ( strpos( $expression, '|' ) === 0 ) {
                $expression = substr($expression, 1);
            // The expression is over
            } else {                                
                break;
            }
            
            // Store a copy to check for keyword on the next step
            $prevExpression = $expression;
            $value = '';
        }
        
        // Add a check for a valid final value
        if ( !$allowNull ) {
            $this->getCodegen()
            ->if($varName . ' === NULL')
                ->rethrow()
            ->endIf();
        }
        
        // Close the nested try/catch blocks
        while( $nested-- > 0) {             
            $this->getCodegen()
            ->endTry();
        }           
        
        return $expression;
    }
    
    public function beforeElement()
    {
    }
    
    public function beforeContent()
    {        
    }
    
    public function afterContent()
    {        
    }
    
    public function afterElement()
    {        
    }
}
