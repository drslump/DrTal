<?php #$Id$
/*
 File: Tal/Context.php

    DrTal - A TAL template engine for PHP
    
 License:

    The GNU General Public License version 3 (GPLv3)
    
    This file is part of DrTal.

    DrTal is free software; you can redistribute it and/or modify it under the
    terms of the GNU General Public License as published by the Free Software
    Foundation; either version 2 of the License, or (at your option) any later
    version.
    
    DrTal is distributed in the hope that it will be useful, but WITHOUT ANY
    WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
    FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
    details.
    
    You should have received a copy of the GNU General Public License along with 
    DrTal; if not, write to the Free Software Foundation, Inc., 51 Franklin
    Street, Fifth Floor, Boston, MA 02110-1301, USA
    
    See bundled license.txt or check <http://www.gnu.org/copyleft/gpl.html>

 Copyright:
    
    copyright (c) 2008 Iván -DrSlump- Montes <http://pollinimini.net>
*/

namespace DrSlump\Tal;

use DrSlump\Tal;

require_once TAL_LIB_DIR . 'Tal/Context/Helper/Repeat.php';

/*
 Class: DrTal::Context
    Default context handling class which stores the variables for the associated
    template and offers helper functions like a path resolver or escaping functions.    

 See also:
    <Tal_Template>
*/
class Context
{
    protected $template;
    protected $stack = array();
    
    protected $dbgTemplate = array();
    protected $dbgNamespaces = array();
    protected $dbgHints = array();
    
    /*
     Constructor: __construct
     
     Arguments:
        $tpl    - The <Tal::Template> instantiating this object
    */
    public function __construct( Template $tpl )
    {
        $this->template = $tpl;
        $this->stack = array(
            array(
                'nothing'   => '',
                'true'      => true,
                'false'     => false,
                'repeat'    => array()
            )
        );
    }
    
    /*
     Method: getTemplate
        Returns the template object associated with this context
        
     Returns:
        A <Tal::Template> object
    */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /*
     Method: push
        Pushes the variables stack (last in first out)
     
    */
    public function push()
    {
        array_push( $this->stack, array() );
    }
    
    /*
     Method: pop
        Pops the variables stack (last in first out)
        
    */
    public function pop()
    {
        array_pop( $this->stack );
    }
    
    /*
     Method: get
        Gets a variable from the context 
     
     Arguments:
        $name   - The name of the variable to get
        
     Returns:
        The variable value or null if the variable name was not found
        
     Note:
        If the context has been pushed this method will seek the variable in all
        the stack levels 
    */
    public function get( $name )
    {
        for ( $i=count($this->stack)-1; $i>=0; $i-- ) {
            if ( isset($this->stack[$i][$name]) ) {
                $v = $this->stack[$i][$name];
                if (is_string($v) && strpos($v, '$TAL-CALLABLE$')===0) {
                    return $v();
                }
                return $v;
            }
        }
        
        return null;
    }
    
    /*
     Method: set
        Sets a variable in the context 
     
     Arguments:
        $name       - The name of the variable to set
        $value      - The desired value for the variable
        $isGlobal?  - If true the variable will be kept even when popping the context
        
     Note:
        If the context has been pushed this method will set the variable in the
        last pushed one. So the variable will be lost when poping the context if
        $isGlobal is false.
    */
    public function set( $name, $value, $isGlobal = false )
    {
        if ($isGlobal) {
            $this->stack[0][$name] = $value;
        } else {
            $this->stack[count($this->stack)-1][$name] = $value;
        }
    }    
    
    
    
    public function setDebugNamespace( $uri, $prefix )
    {
        $this->dbgNamespaces[$uri] = $prefix;
    }    
    
    
    
    
    
    public function initRepeat( $name, $value )
    {
        $this->stack[0]['repeat'][$name] = new Context\Helper\Repeat($value);
        
        return $this->stack[0]['repeat'][$name];
    }
    
    public function closeRepeat( $name )
    {
        unset($this->stack[0]['repeat'][$name]);
    }
    
    public function error($msg, $hilite = null)
    {            
        // Calculate the line number by traversing the tags
        $lnNo = 0;
        $pos = 0;
        $hint = array_shift($this->dbgHints);
        $hint = '<' . $hint[1];
        do {
            $ln = $this->dbgTemplate[$lnNo];
            
            // Try to find the openning tag in the line
            $pos = stripos($ln, $hint, $pos);
            // If not found we proceed to the next line
            if ($pos === false) {
                $pos = 0;
                $lnNo++;
            // If found get the following tag hint
            } else {
                if (empty($this->dbgHints)) {
                    break;
                }
                
                $hint = array_shift($this->dbgHints);
                $hint = '<' . $hint[1];
            }
            
            // If the algorithm couldn't follow the tags then report the hinted line number
            if ($lnNo >= count($this->dbgTemplate)) {
                $hint = array_pop($this->dbgHints);
                $lnNo = $hint[0];
                break;
            }
        } while(true);        
     
        $e = new Tal\Exception($msg);        
        $e->setTemplate($this->template);
        $e->setLn($lnNo);
        $e->setHilite($hilite);
        
        throw $e;
    }
    
    public function path( $path, $throw = true, $nocall = false )
    {
        $parts = explode('/', $path);
        
        $part = array_shift($parts);
        $base = $this->get( $part );
        
        if ($base === null) {
            if ($throw) {
                $this->error("Part '$part' not found in path '$path'", $part );
            } else {
                return null;
            }
        }
            
        foreach ( $parts as $part ) {
            
            $isCallable = false;
            
            if ( is_array($base) ) {
                if ( !is_null($result = $base[$part]) ) {
                    $base = $result;
                } else {
                    if ($throw) {
                        throw new Tal\Exception( "(array) Part '$part' not found in '$path'" );
                    } else {
                        return null;
                    }
                }
            } else if (is_object($base)) {
                if ( property_exists($base, $part) ) {
                    $base = $base->$part;
                } else if ( $base instanceof ArrayObject && !is_null($result = $base[$part]) ) {
                    $base = $result;  
                } else if ( /*property_exists($base, '__get') && */!is_null($result = $base->$part) ) {
                    $base = $result;
                } else if ( is_callable( array($base, $part), false, $callable ) ) {
                    $isCallable = true;
                    $base = $callable;
                } else if ( $throw ) {
                    throw new Tal\Exception( "(object) Part '$part' not found in '$path'" );
                } else {
                    return null;
                }
            } else if ( $throw ) {
                $this->error("Part '$part' not found in path '$path'", $part );
            } else {
                return null;
            }
        }
        
        if (!empty($isCallable)) {
            if ($nocall) {
                return '$TAL-CALLABLE$' . $base;
            } else {
                return $base();
            }
        } else {
            return $base;
        }
    }
    
    public function exists( $path )
    {
        try {
            $this->path($path);
            return true;
        } catch (Tal\Exception $e) {
            return false;
        }
    }

    public function write( $str, $escape = true )
    {
        echo $escape ? $this->escape($str) : $str;
    }

    public function escape( $name )
    {
        return htmlspecialchars( $name );
    }
    
    
    public function dbgTemplate($lines)
    {
        $this->dbgTemplate = $lines;
    }
    
    /*
        In debug mode this is used to indicate at runtime the position in the
        template.
    */
    public function dbgHint($lineNo, $tag = null){
        $this->dbgHints[] = array( $lineNo, $tag );
    }
}