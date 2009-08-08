<?php

namespace DrSlump\Tal\Parser;

use DrsLump\Tal\Parser\OpcodeList;

abstract class Attribute
{
    protected $_element;
    protected $_name;
    protected $_value;
    protected $_removed;
    
    public function __construct( Element $element, $name, $value)
    {
        $this->_element = $element;
        $this->_name = $name;
        $this->_value = $value;
        $this->_removed = true;
    }
    
    public function getElement()
    {
        return $this->_element;
    }
    
    public function getName()
    {
        if ( $pos = strpos($this->_name, ':') ) {
            return substr( $this->_name, $pos+1 );
        } else {
            return $this->_name;
        }
    }
    
    public function getRawName()
    {
        return $this->_name;
    }
    
    public function getValue()
    {
        return $this->_value;
    }
    
    public function getPrefix()
    {
        return substr( $this->_name, 0, (int)strpos($this->_name, ':') );
    }
    
    public function isRemoved($remove = null)
    {
        if (NULL !== $remove) {
            $this->_removed = $remove;
        }
        return $this->_removed;
    }
    
    protected function getProgram()
    {
        return $this->getElement()->getProgram();
    }
    
    /*
     Method: tales
        Evaluates a tales expression
        
     Arguments:
        $exp       - the tales expression to evaluate 
     
     Returns:
        A <Tal_Parser_Tales> object
    */
    protected function _tales( $exp )
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
        
        $handler = $this->getElement()->getParser()->getTales( $modifier );
        if ( !$handler ) {
            throw new Parser\Exception('Tales handler "' . $modifier . '" was not found');
        }

        $handler = new $handler($this->getElement()->getParser(), $exp);
        $handler->evaluate();
        return $handler;
    }
    
    
    /*
        The expression can have alternates ('|') so we need to build a runtime
        expression which checks the alternatives in the given order and gets the
        first valid one or exits with an exception if no alternative is valid.
        It generates something like this (for "my/path | my/alternative/path"):
        
        $varname = NULL;
        $varname = $ctx->path('my/path', false);
        if (!$varname):
            $varname = $ctx->path('my/alternative/path', false);
            // if the are no more alternatives check if we actually have a value
            if ($varname === NULL):
                $ctx->error();
            endif;
        endif;
        
        
        Another possible way to implement this is by using exceptions. Throwing an
        exception however is very expensive, so this alternative is faster for
        alternations where the first expression always matches but slower if
        there first expression usually fails.
        Note: To speed it up we create a reusable exception ($_exception)
        
        try {
            $varname = $ctx->path('my/path');
            if (!$varname) throw $_exception;
        } catch (Exception $e) {
            try {
                $varname = $ctx->path('my/alternative/path');
                if (!$varname) throw $_exception;
            } catch(Exception $e) {
                // if the are no more alternatives check if we actually have a value
                if ($varName === NULL)
                    $ctx->error();
            }
        }
        
        
        The "if" based solution is the one implemented since it's has a constant
        performance, not being very much affected by which expression returns a
        value in the alternation.
    */
    
    protected function doAlternates( $exp, $varname = '$_value', $default = '', $allowNull = false)
    {
        $prog = $this->getProgram();
        $isDefault = false;
        $nested = 0;
        $statement = new OpcodeList();
        $prevExp = '';
        
        // Initialize the variable with a null value to detect if it was never set
        //$prog->code("$varname = NULL;");
        
        while( $handler = $this->_tales($exp) ) {
            
            // Detect the default keyword and if pressent overwrite the value
            if ( preg_match('/^\s*default\b/i', $exp) ) {
                $isDefault = true;
                $statement->code($default);
            } else {
                $isDefault = false;
                $statement->appendList($handler->getOpcodes());
            }
            
            // Get the reduced expression
            $prevExp = trim($exp);
            $exp = $handler->getExpression();
            
            // If it is just a prefix to an expression keep evaluating it
            if ($handler->isPrefix()) {
                continue;
            }
            
            // Check if the tales expression was unsuccessfull
            $prog
            ->code('$_is_default = ' . ($isDefault ? 'true' : 'false') . ';')
            ->assign($varname, $statement)
            ->if("!$varname");
            $nested++;
            

            // Check if an alternate is following
            if ( strpos( $exp, '|' ) === 0 ) {
                $exp = substr($exp, 1);
            } else {                                
                break;
            }
            
            $statement = new OpcodeList();
        }
        
        // Remove deepest If since it'll be useless
        $prog->pop();
        $nested--;
        
        // Add a check for a valid final value if no default is used
        if ( !$isDefault && !$allowNull ) {
            $prog->if("$varname === NULL")
                ->context('error', array('Expression "' . $prevExp . '" failed to return a value', $prevExp))
            ->endIf();
        }
        
        // Close nested ifs
        while ($nested--) {
            $prog->endIf();
        }
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
