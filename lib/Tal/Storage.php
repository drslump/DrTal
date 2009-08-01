<?php #$Id$
/*
 File: Tal/Storage.php

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

/*
 Class: Tal::Storage
    Abstract class defining the basic methods for a storage adapter

 See also:
    <Tal_Storage_File>, <Tal_Storage_String>
*/
abstract class Storage {
    
    protected $options;
    protected $repositories;
    
    /*
     Constructor: __construct
        Class constructor
        
     Arguments:
        $options?       - adapter options
        $repositories?  - template repositories
    */
    public function __construct( $options = array(), $repositories = array() )
    {
        $this->options = $options;
        $this->repositories = $repositories;
    }
    
    /*
     Method: setOption
        Sets an adapter option
        
     Arguments:
        $name   - option to set
        $value  - new option value     
    */
    public function setOption( $name, $value )
    {
        $this->options[$name] = $value;
    }
    
    /*
     Method: getOption
        Gets an adapter option
        
     Arguments:
        $name   - option to get
        
     Returns:
        Option value
    */
    public function getOption( $name )
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }
    
    /*
     Method: setRepositories
        Sets the adapter repositories
        
     Arguments:
        $path   - An array with the new repositories (a string is also accepted)
    */    
    public function setRepositories( $path )
    {
        if ( !is_array($path) ) {
            $path = array( $path );
        }
        
        $this->repositories = $path;
    }
    
    /*
     Method: addRepositories
        Adds new repositories
        
     Arguments:
        $path   - An array with the new repositories (a string is also accepted)
    */
    public function addRepositories( $path )
    {
        if ( !is_array($path) ) {
            $path = array($path);
        }
        
        $this->repositories = array_merge($this->repositories, $path);
    }
    
    /*     
     Method: getRepositories
        Returns the current repositories for this adapter
        
     Returns:
        the current repositories
    */
    public function getRepositories()
    {
        return $this->repositories;
    }
    
    /*
     Method: find
        Locates a template and instantiates a template object associated with it
     
     Arguments:
        $tplName    - The template filename or uri to locate
        $tplClass   - The <Tal_Template> class name to instantiate
     
     Returns:
        A <Tal_Template> object if successful, false if not
        
     Throws:
        <Tal_Storage_Exception> if an unexpected error was found
    */
    abstract public function find( $tplName, $tplClass );

    /*
     Method: load
        Loads the template contents
        
     Arguments:
        $tplName    - the template filename or uri to load
        
     Return:
        the template contents
        
     Throws:
        <Tal_Storage_Exception> if an unexpected error was found     
        
     Notes:
        this function must be used after a succesfull call to <find>
    */
    abstract public function load( $tplName );

    /*
     Method: isCurrent
        Checks if the compiled template is still valid
        
     Arguments:
        $tplName    - the template filename or uri to check
        
     Return:
        true if the compiled template is current, false if not
        
     Throws:
        <Tal_Storage_Exception> if an unexpected error was found     
        
     Notes:
        this function must be used after a succesfull call to <find>
    */
    abstract public function isCurrent( $tplName );

    /*
     Method: getScriptStream
        Gets the compiled template stream path
        
     Arguments:
        $tplName    - the template filename or uri to load
        
     Return:
        the compiled template stream path
        
     Throws:
        <Tal_Storage_Exception> if an unexpected error was found     
        
     Notes:
        this function must be used after a succesfull call to <find>
    */
    abstract public function getScriptStream( $tplName );

    /*
     Method: getScriptIdent
        Gets the compiled template unique identifier
        
     Arguments:
        $tplName    - the template filename or uri to load
        
     Return:
        the compiled template identifier
        
     Throws:
        <Tal_Storage_Exception> if an unexpected error was found     
        
     Notes:
        this function must be used after a succesfull call to <find>
    */
    abstract public function getScriptIdent( $tplName );
    
}