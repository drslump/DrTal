<?php

namespace DrSlump\Tal\Parser\Generator\Base\Ns;

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
    
    protected function getWriter()
    {
        return $this->element->getWriter();
    }
    
    /*
     Method: tales
        Evaluates a tales expression
        
     Arguments:
        $exp       - the tales expression to evaluate 
     
     Returns:
        A <Tal_Parser_Generator_Base_Tales> object
    */
    protected function tales( $exp )
    {
        $exp = ltrim( $exp );
        
        // Check expression type
        if ( strpos($exp, "'")===0 ) {
            $modifier = 'string';
        } else if ( strpos($exp, '!' )===0) {
            $modifier = 'not';
            $exp = substr( $exp, 1 );
        } else if ( preg_match( '/^([A-Za-z][A-Za-z_-]*[A-Za-z_]*):/', $exp, $m ) ) {
            $modifier = $m[1];
            $exp = ltrim( substr( $exp, strlen($m[0]) ) );
        } else {
            $modifier = 'path';
        }
        
        $handler = $this->element->getParser()->getTales( $modifier );
        if ( !$handler ) {
            throw new Parser\Exception('Tales handler "' . $modifier . '" was not found');
        }
        
        $handler = new $handler($this->getWriter(), $exp);
        $handler->evaluate();
        return $handler;
    }
    
    /*
        The expression can have alternates ('|') so we need to build a runtime
        expression which checks the alternatives in the given order and gets the
        first valid one or exits with an exception if no alternative is valid.
        It generates something like this (for "my/path | my/alternative/path"):
        
        $varname = NULL;
        try {
            if (!$varName = $ctx->path('my/path'))
                throw new Tal\Exception();
        } catch (Tal\Exception $e) {
            try {
                if (!$varName = $ctx->path('my/alternative/path'))
                    throw new Tal\Exception();
            } catch (Tal\Exception $e) {
                // if the are no more alternatives check if we actually have a value
                if ($varName === null)
                    throw $e;                    
            }
        }
    */
    protected function doAlternates( $expression, $varName, $default = '', $allowNull = false )
    {
        $w = $this->getWriter();
        
        // Initialize the variable
        $w->php($varName . ' = NULL;')->EOL();
        
        $nested = 0;
        $prevExpression = $expression;
        $value = '';
        
        while ( $handler = $this->tales($expression) ) {
            
            $expression = $handler->getExpression();
            
            // Append the modifier value to the chain
            $value .= $handler->getValue();
            
            // If it's just a prefix there is no need to check alternation
            if ( $handler->isPrefix() ) {
                continue;
            }
            
            // Detect the default keyword and if pressent overwrite the value
            if ( preg_match('/^\s*default\b/i', $prevExpression) ) {
                $value = "'" . addcslashes($default, '\'\\') . "'";
            }
            
            $w->try()
                ->if('!(' . $varName . ' = ' . $value . ')')
                    ->throw('Tal\\Exception')
                ->endIf()
            ->catch('Tal\\Exception');
            
            $nested++;
            
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
            $w->if($varName . ' === NULL')
                ->rethrow()
            ->endIf();
        }
        
        // Close the nested try/catch blocks
        while( $nested-- > 0) {
            $w->endTry();
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
