<?php #$Id$
/*
 File: DrTal.php

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
*/


Namespace DrSlump;

if ( !defined('DRTAL_INCLUDE_BASE') ) {
    define( 'DRTAL_INCLUDE_BASE', __DIR__ . DIRECTORY_SEPARATOR );
}

require_once DRTAL_INCLUDE_BASE . 'DrTal/Exception.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Storage/File.php';
require_once DRTAL_INCLUDE_BASE . 'DrTal/Template/Xhtml.php';


/*
 Class: DrTal

*/
class DrTal
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


    protected $templateClass = 'DrTal::Template::Xhtml';
    protected $storages = array();

    /*
     Method: __construct
        Class constructor

    */
    private function __construct()
    {
        $storage = new DrTal\Storage\File( array(
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
        the <DrTal> singleton instance
    */
    static public function getInstance()
    {
        static $instance;

        if (!$instance) {
            $instance = new DrTal();
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
        and the <DrTal::Storage> as value
    */
    public function getStorages()
    {
        $storages = array();
        foreach ( $this->storages as $name => $storage )
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
        if found a <DrTal::Storage> object, if not false
    */
    public function getStorage( $name )
    {
        if ( isset($this->storages[$name]) ) {
            return $this->storages[$name]['object'];
        }
        
        return false;
    }


    /*
     Method: registerStorage
        Registers a new storage (or updates an existing one)

     Arguments:
        $name   - The choosen name for the storage
        $obj    - The <DrTal::Storage> object to register
        $prio   - The priority of this storage (see <DrTal> priority constants)

     Returns:
        always true
    */
    public function registerStorage( $name, DrTal::Storage $obj, $prio = self::PRIORITY_MEDIUM )
    {
        $this->storages[$name] = array(
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
        if ( isset($this->storages[$name]) ) {
            unset($this->storages[$name]);
            return true;
        }

        return false;
    }

    /*
     Method: setClass
        Static method to inject a custom <DrTal::Template> class name to be used
        when instantiating a template.

     Arguments:
        $tplClass   - The <DrTal::Template> descendant class to use
    */
    static public function setClass( $tplClass )
    {
        $self = self::getInstance();
        $self->templateClass = $tplClass;
    }

    /*
     Method: load
        Static method to find a template source file using the given storage
        adapter or searching for it in the currently registered storages

     Arguments:
        $tplFile    - the template filename or uri to get
        $storage?   - a <DrTal::Storage> object to be queried for the template

     Returns:
        a <DrTal::Template> object repressenting the choosen template

     Throws:
        <DrTal::Exception> if it was not possible to find the template
    */
    static public function load( $tplFile, $storage = null )
    {
        $self = self::getInstance();

        if ($storage === null && empty($self->storages)) {
            throw new DrTal::Exception( 'No storages are registered. Register at least one to be able to load a template' );
        }

        if ( is_string($storage) ) {

            if ( !isset($self->storages[$storage]) ) {
                throw new DrTal::Exception( 'The supplied storage "' . $storage . '" is not registered' );
            }

            $storage = $self->storages[$storage];
        }

        // Check using the supplied resolver object
        if ( $storage instanceof DrTal::Storage ) {

            $tplObj = $storage->find( $tplFile, $self->templateClass );

        } else {

            foreach ( DrTal::sortByPriority($self->storages) as $storage ) {
                if ( $tplObj = $storage['object']->find( $tplFile, $self->templateClass ) ) {
                    break;
                }
            }
        }

        if ( !$tplObj ) {
            throw new DrTal::Exception( 'The template "' . $tplFile . '" could not be found' );
        }

        return $tplObj;
    }

    /*
     Method: string
        Static method to generate a template from a template source given as a string

     Arguments:
        $tplString  - A string with the template contents
        $storage?   - A <DrTal::Storage> object used to work with the template. By default
                        it will use <DrTal::Storage::String>

     Returns:
        A <DrTal::Template> object

     Throws:
        <DrTal::Exception> if it was not possible to create the template
    */
    static public function string( $tplString, DrTal::Storage $storage = null )
    {
        if ( !$storage ) {
            require_once DRTAL_INCLUDE_BASE . 'DrTal/Storage/String.php';

            $storage = new DrTal::Storage::String( array(
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
                'priority' => DrTal::PRIORITY_MAXIMUM
                ...
            ),
            ...
        )
        (end)
    */
    static public function sortByPriority( $array ) {
        static $sortFunc;

        if ( !$sortFunc ) {
            $sortFunc = create_function('$a,$b', '
                if ( $a["priority"] == $b["priority"] )
                    return 0;
                return ( $a["priority"] < $b["priority"] );
            ');
        }

        usort( $array, $sortFunc );

        return $array;
    }
}