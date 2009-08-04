<?php #$Id$
/*
 File: Tal.php

    Tal - A TAL template engine for PHP

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

    copyright (c) 2008-2009 Iván -DrSlump- Montes <http://pollinimini.net>

 Authors:

    Iván -DrSlump- Montes <http://pollinimini.net>


 Requeriments:

    - PHP 5.3 or newer
    - PHP's Tidy extension (only for <DrTal::Template::HtmlTidy>)


 Todo:
    - Check if the implementation of metal:extend-macro is feasible
    - Clean up the sources
    - Improve error handling and make sure internal exceptions are captured
    - Check conformance with TAL 1.4 specification (http://wiki.zope.org/ZPT/TALSpecification14)
    - Check conformance with METAL 1.1 specification (http://wiki.zope.org/ZPT/METALSpecification11)
    - Start writing some tests to fine tune the public API
    - Adapt PHPTAL's unit tests
    - Implement the PHPTAL emulation class
    - Use SPL's class loader instead of requiring files by hand (at least for the parser part)
*/


/*
 Namespace: DrSlump
*/
Namespace DrSlump;

Use DrSlump\Tal\Storage;
Use DrSlump\Tal\Template;

if ( !defined('TAL_LIB_DIR') ) {
    define( 'TAL_LIB_DIR', __DIR__ . DIRECTORY_SEPARATOR );
}


require_once TAL_LIB_DIR . 'Tal/Exception.php';
require_once TAL_LIB_DIR . 'Tal/Storage/File.php';
require_once TAL_LIB_DIR . 'Tal/Template/Xhtml.php';


/*
 Class: Tal

*/
class Tal
{
    const VERSION_SIGNATURE         = '01';

    const ANY_NAMESPACE             = '*';
    const ANY_ELEMENT               = '*';
    const ANY_ATTRIBUTE             = '*';

    const PRIORITY_MAXIMUM          = 9;
    const PRIORITY_VERYHIGH         = 8;
    const PRIORITY_HIGH             = 7;
    const PRIORITY_MEDIUM           = 5;
    const PRIORITY_LOW              = 3;
    const PRIORITY_VERYLOW          = 2;
    const PRIORITY_MINIMUM          = 1;


    protected $_defaultTemplateClass = 'DrSlump\\Tal\\Template\\Xhtml';
    protected $_storages = array();

    /*
     Method: __construct
        Class constructor

    */
    private function __construct()
    {
        // Initialize the file storage by default
        $storage = new Storage\File( array(
            'path-prefix'   => 'DrTal_',
            'file-prefix'   => 'DrTal_' . self::VERSION_SIGNATURE,
            'extension'     => 'php',
            'nesting-levels'=> '3'
        ));

        self::registerStorage( 'file', $storage );
    }

    /*
     Method: getInstance
        static class singleton getter

     Returns:
        the <Tal> singleton instance
    */
    static public function getInstance()
    {
        static $instance;

        if (!$instance) {
            $instance = new Tal();
        }

        return $instance;
    }

    /*
     Method: debugging
        static method to set or get debugging mode
        
     Arguments:
        $enabled    - If set to false it will disable the debug mode, if true it's
                        enabled. If not set it will return the current debug mode.
            
     Returns:
        the current debugging mode
    */
    static public function debugging( $enabled = null )
    {
        static $debugging = false;

        if ( null !== $enabled ) {
            $debugging = $enabled;
        }

        return $debugging;
    }

    
    /*
     Method: getStorages
        Returns an array with all the storages currently registered

     Returns:
        An associative array with the registered storages with the name as key
        and the <Tal::Storage> as value
    */
    public function getStorages()
    {
        $storages = array();
        foreach ( $this->_storages as $name => $storage )
        {
            $storages[$name] = $storage['object'];
        }

        return $storages;
    }

    /*
     Method: getStorage
        Returns a given registered storage by its name

     Arguments:
        $name   - the storage name to get

     Returns:
        if found a <Tal::Storage> object, if not false
    */
    public function getStorage( $name )
    {
        if ( isset($this->_storages[$name]) ) {
            return $this->_storages[$name]['object'];
        }
        
        return false;
    }


    /*
     Method: registerStorage
        Registers a new storage (or updates an existing one)

     Arguments:
        $name   - The choosen name for the storage
        $obj    - The <Tal::Storage> object to register
        $prio   - The priority of this storage (see <Tal> priority constants)

     Returns:
        always true
    */
    public function registerStorage( $name, Tal\Storage $obj, $prio = self::PRIORITY_MEDIUM )
    {
        $this->_storages[$name] = array(
            'object'    => $obj,
            'priority'  => $prio,
        );
        
        return true;
    }

    /*
     Method: unregisterStorage
        Unregisters a storage from DrTal

     Arguments:
        $name   - The storage name to unregister

     Returns:
        true if the storage existed and was removed, false if not
    */
    public function unregisterStorage( $name )
    {
        if ( isset($this->_storages[$name]) ) {
            unset($this->_storages[$name]);
            return true;
        }

        return false;
    }

    /*
     Method: setTemplateClass
        Static method to inject a custom <Tal::Template> class name to be used
        when instantiating a template.

     Arguments:
        $tplClass   - The <Tal::Template> descendant class to use
    */
    static public function setTemplateClass( $tplClass )
    {
        $self = self::getInstance();
        $self->_defaultTemplateClass = $tplClass;
    }

    /*
     Method: getTemplateClass
        Static method to get the current default <Tal::Template> class name to
        be used when instantiating a template.

     Returns:
        The <Tal::Template> descendant class to use
    */
    static public function getTemplateClass()
    {
        $self = self::getInstance();
        return $self->_defaultTemplateClass;
    }
    
    /*
     Method: load
        Static method to find a template source file using the given storage
        adapter or searching for it in the currently registered storages

     Arguments:
        $tplFile    - the template filename or uri to get
        $storage?   - a <Tal::Storage> object to be queried for the template

     Returns:
        a <Tal::Template> object repressenting the choosen template

     Throws:
        <Tal::Exception> if it was not possible to find the template
    */
    static public function load( $tplFile, $storage = null )
    {
        $self = self::getInstance();

        if ($storage === null && empty($self->_storages)) {
            throw new Tal\Exception( 'No storages are registered. Register at least one to be able to load a template' );
        }

        if ( is_string($storage) ) {

            if ( !isset($self->_storages[$storage]) ) {
                throw new Tal\Exception( 'The supplied storage "' . $storage . '" is not registered' );
            }

            $storage = $self->_storages[$storage];
        }

        // Check using the supplied resolver object
        if ( $storage instanceof Storage ) {

            $tplObj = $storage->find( $tplFile, $self->_defaultTemplateClass );

        } else {

            foreach ( Tal::sortByPriority($self->storages) as $storage ) {
                if ( $tplObj = $storage['object']->find( $tplFile, $self->templateClass ) ) {
                    break;
                }
            }
        }

        if ( !$tplObj ) {
            throw new Tal\Exception( 'The template "' . $tplFile . '" could not be found' );
        }

        return $tplObj;
    }

    /*
     Method: string
        Static method to generate a template from a template source given as a string

     Arguments:
        $tplString  - A string with the template contents
        $storage?   - A <Tal::Storage> object used to work with the template. By default
                        it will use <Tal::Storage::String>

     Returns:
        A <Tal::Template> object

     Throws:
        <Tal::Exception> if it was not possible to create the template
    */
    static public function string( $tplString, Storage $storage = null )
    {
        // If no storage given then create a string based one
        if ( !$storage ) {
            include_once TAL_LIB_DIR . 'Tal/Storage/String.php';
            
            $storage = new Storage\String( array(
                'path-prefix'   => 'DrTal_',
                'file-prefix'   => 'DrTal_' . self::VERSION_SIGNATURE,
                'extension'     => 'php',
                'nesting-levels'=> '0'
            ));
        }

        return self::load( $tplString, $storage );
    }

    /*
     Method: sortByPriority
        Static helper method to sort an array by priorities

     Arguments:
        $array      - The array to sort

     Returns:
        The sorted array

     Notes:
        The array format should be as follows:
        (start code)
        array(
            array(
                'priority' => Tal::PRIORITY_MAXIMUM
                ...
            ),
            ...
        )
        (end)
    */
    static public function sortByPriority( $array ) {
        /*
        static $sortFunc;

        if ( !$sortFunc ) {
            $sortFunc = create_function('$a,$b', '
                if ( $a["priority"] == $b["priority"] )
                    return 0;
                return ( $a["priority"] < $b["priority"] );
            ');
        }
        */
        
        $sortFunc = function($a, $b, $p = 'priority'){
            return $a[$p] == $b[$p] ? 0 : $a[$p] < $b[$p];
        };

        usort( $array, $sortFunc );

        return $array;
    }
}