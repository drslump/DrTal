<?php #$Id$
/*
 File: DrTal/Context.php

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

namespace DrTal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Context/Helper/Repeat.php';

/*
 Class: DrTal::Context
    Default context handling class which stores the variables for the associated
    template and offers helper functions like a path resolver or escaping functions.    

 See also:
    <DrTal_Template>
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
        $tpl    - The <DrTal::Template> instantiating this object
    */
    public function __construct( Template $tpl )
    {
        $this->template = $tpl;
        $this->stack = array(
            array(
                'nothing'   => '',
                'repeat'    => array()
            )
        );
    }
    
    /*
     Method: getTemplate
        Returns the template object associated with this context
        
     Returns:
        A <DrTal::Template> object
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
            if ( isset($this->stack[$i][$name]) )
                return $this->stack[$i][$name];
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
    
    
    
    public function setDebugTemplate( $source )
    {
        $source = preg_replace('/\s+/s', '', $source );
        $source = gzinflate( base64_decode($source) );
        $this->dbgTemplate = $source;
    }
    
    public function setDebugNamespace( $uri, $prefix )
    {
        $this->dbgNamespaces[$uri] = $prefix;
    }
    
    public function setDebugHint( $hint )
    {
        $this->dbgHints[] = $hint;
    }
    
    
    public function initRepeat( $name, $value )
    {
        $this->stack[0]['repeat'][$name] = new DrTal_Context_Helper_Repeat($value);
        
        return $this->stack[0]['repeat'][$name];
    }
    
    public function closeRepeat( $name )
    {
        unset($this->stack[0]['repeat'][$name]);
    }
    
    public function path( $path )
    {
        $parts = explode('/', $path);
        
        $part = array_shift($parts);
        $base = $this->get( $part );
        
        if ($base === null) {
            throw new DrTal_Exception( "Part '$part' not found in '$path'" );
        }
            
        foreach ( $parts as $part ) {
            
            //echo "<h3>$path - $part</h3>";
            //var_dump($base);            
            
            if ( is_array($base) ) {
                if ( !is_null($result = $base[$part]) ) {
                    $base = $result;
                } else {
                    throw new DrTal_Exception( "(array) Part '$part' not found in '$path'" );
                }
            } else if (is_object($base)) {
                if ( property_exists($base, $part) ) {
                    $base = $base->$part;
                } else if ( $base instanceof ArrayObject && !is_null($result = $base[$part]) ) {
                    $base = $result;  
                } else if ( /*property_exists($base, '__get') && */!is_null($result = $base->$part) ) {
                    $base = $result;
                } else if ( is_callable( array($base, $part), false, $callable ) ) {
                    $base = $callable();
                } else {                
                    throw new DrTal_Exception( "(object) Part '$part' not found in '$path'" );
                } 
            } else {                
                throw new DrTal_Exception( "Part '$part' not found in '$path'" );
            } 
        }
        
        return $base;
    }

    public function escape( $name )
    {
        return htmlentities( $name );
    }
}