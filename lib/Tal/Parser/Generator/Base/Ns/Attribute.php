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
        if (!($varName = $ctx->path('my/path'))) {
            if (!($varName = $ctx->path('my/alternative/path'))) {
                if ($varName === NULL) {
                    throw new Tal\Exception('Null value');
                }
            }
        }
    */
    protected function doAlternates( $exp, $varname, $default = '', $allowNull = false)
    {
        $w = $this->getWriter();
        $isDefault = false;
        $nested = 0;
        $value = '';
        
        // Initialize the variable with a null value to detect if it was never set
        $w->code("$varname = NULL;");
        
        while( $handler = $this->tales($exp) ) {
            
            // Detect the default keyword and if pressent overwrite the value
            if ( preg_match('/^\s*default\b/i', $exp) ) {
                $isDefault = true;
                $value .= $default;
            } else {
                $isDefault = false;
                $value .= $handler->getValue();
            }
            
            // Get the reduced expression
            $exp = $handler->getExpression();
            
            // If it is just a prefix to an expression keep evaluating it
            if ($handler->isPrefix()) {
                continue;
            }
            
            // Check if the tales expression was unsuccessfull
            $w->code('$_tal_is_default = ' . ($isDefault ? 'true' : 'false') . ';')
            ->if("!($varname = $value)");
            $nested++;
            

            // Check if an alternate is following
            if ( strpos( $exp, '|' ) === 0 ) {
                $exp = substr($exp, 1);
            } else {                                
                break;
            }
            
            $value = '';
        }
        
        // Add a check for a valid final value
        if ( !$allowNull ) {
            $w->if("$varname === NULL")
                ->throw('Tal\\Exception', array("'Null value found'"))
            ->endIf();
        }        
        
        // Close nested ifs
        while ($nested--) {
            $w->endIf();
        }
    }

    protected function doAlternates2( $expression, $varName, $default = '', $allowNull = false )
    {
        $w = $this->getWriter();
        
        // Initialize the variable
        $w->code($varName . ' = NULL;');
        
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
                //$value = "'" . addcslashes($default, '\'\\') . "'";
                $value = $default;
            }
            
            $w->try()
                ->if('!(' . $varName . ' = ' . $value . ')')
                    ->throw('Tal\\Exception')
                ->endIf()
            ->catch('Tal\\Exception', '$e');
            
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
                ->throw('Tal\\Exception')
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
