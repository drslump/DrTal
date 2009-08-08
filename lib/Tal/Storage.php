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
    
    protected $_options = array();
    protected $_repositories = array();
    
    /*
     Constructor: __construct
        Class constructor
        
     Arguments:
        $options?       - adapter options
        $repositories?  - template repositories
    */
    public function __construct( $options = array(), $repositories = array() )
    {
        $this->setOption($options);
        $this->addRepositories($options);
    }
    
    /*
     Method: setOption
        Sets an adapter option
        
     Arguments:
        $name   - option to set (or an associative array)
        $value  - new option value (optional if $name is an array)    
    */
    public function setOption( $name, $value = null)
    {
        if (!is_array($name)) {
            $name = array($name);
        }
        $this->_options = array_merge($this->_options, $name);
    }
    
    /*
     Method: getOption
        Gets an adapter option
        
     Arguments:
        $name   - option to get. If not set then the options array is returned
        
     Returns:
        Option value or the options array
    */
    public function getOption( $name = NULL )
    {
        if ( $name === NULL ) {
            return $this->_options;
        }
        
        return isset($this->_options[$name]) ? $this->_options[$name] : null;
    }
    
    /*
     Method: clearRepositories
        Empties the repositories list
    
    */    
    public function clearRepositories()
    {
        $this->_repositories = array();
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
        
        $this->_repositories = array_merge($this->_repositories, $path);
    }
    
    /*     
     Method: getRepositories
        Returns the current repositories for this adapter
        
     Returns:
        the current repositories
    */
    public function getRepositories()
    {
        return $this->_repositories;
    }
    
    /*
     Method: find
        Locates a template and instantiates a template object associated with it
     
     Arguments:
        $tplName    - The template filename or uri to locate
     
     Returns:
        A <Tal_Template> object if successful, false if not
        
     Throws:
        <Tal_Storage_Exception> if an unexpected error was found
    */
    abstract public function find( $tplName );

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
     Method: save
        Save the template contents
        
     Arguments:
        $tplName    - the template filename or uri to load
        
     Return:
        the template contents
        
     Throws:
        <Tal_Storage_Exception> if an unexpected error was found
        
     Notes:
        this function must be used after a succesfull call to <find>
    */
    abstract public function save( $tplName, $tplContents);
    

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
     Method: getScriptPath
        Gets the compiled template path
        
     Arguments:
        $tplName    - the template filename or uri to load
        
     Return:
        the compiled template stream path
        
     Throws:
        <Tal_Storage_Exception> if an unexpected error was found     
        
     Notes:
        this function must be used after a succesfull call to <find>
    */
    abstract public function getScriptPath( $tplName );

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