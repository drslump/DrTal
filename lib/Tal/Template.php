<?php #$Id$
/*
 File: Tal/Template.php

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

require_once TAL_LIB_DIR . 'Tal/Context.php';


/*
 Class: Tal::Template
    Abstract class defining the basic methods for a template

 See also:
    <Tal::Template::Xml>, <Tal::Template::Xhtml>, <Tal::Template::HtmlTidy>
    and <Tal::Context>
*/
abstract class Template {
    
    protected $_parser;
    protected $_context;
    protected $_storage;
    protected $_serializer;
    protected $_tplName;
    protected $_prepared = false;
    
    
    /*
     Constructor: __construct
        Class constructor to initialize the relationship with an storage
     
     Arguments:
        $storage    - A <Tal_Storage> related to this template
        $tplName    - The template name as understood by the given storage
     */
    public function __construct( Storage $storage, $tplName )
    {
        $this->_storage = $storage;
        $this->_tplName = $tplName;
        
        $this->_context = new Context($this);
    }
    
    public function getName()
    {
        return $this->_tplName;
    }
    
    /*
     Method: getSource
        Returns a string with the contents of the template. This method is
        only used when the templated needs to be compiled.
        
     Returns:
        A string with the template source
        
     Note:
        Extend this method to include your pre-parsing filters
        
     See also:
        <Tal_Storage->load>
    */
    public function getSource()
    {
        $tpl = $this->_storage->load( $this->_tplName );
        
        return $tpl;
    }
    
    /*
     Method: getScriptPath
        Returns the compiled template path
        
     Returns:
        the compiled template path
        
     See also:
        <Tal_Storage->getScriptPath>        
    */
    public function getScriptPath()
    {
        return $this->_storage->getScriptPath( $this->_tplName );
    }
    
    /*
     Method: getScriptIdent
        Returns the compiled template unique identifier
     
     Returns:
        the compiled template unique identifier
        
     See also:
        <Tal_Storage->getScriptIdent>
    */
    public function getScriptIdent()
    {
        return $this->_storage->getScriptIdent( $this->_tplName );        
    }
    
    public function getContext()
    {
        return $this->_context;
    }
    
    public function setContext(Tal\Context $context)
    {
        $this->_context = $context;
    }
    
    /*
     Method: initParser
        Private method to setup the parser.
        
     Note:
        Extend this method if your need to configure the parser for your needs.
        
        (start code)
        @php
        protected function initParser() {
            parent::_initParser();
            $this->_parser->registerNamespace( new MyCustomNamespace() );
            $this->_parser->registerEntity( 'version', Tal::VERSION_SIGNATURE );
        }
        (end code)
    */
    protected function _initParser()    
    {
        // Setup an autoloader
        spl_autoload_register(function($class){
            if (strpos($class, __NAMESPACE__) === 0) {
                $class = substr($class, strlen(__NAMESPACE__));
                $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
                include_once TAL_LIB_DIR . 'Tal' . $class . '.php';
            }
        });
        
        // Initialize the parser
        $this->_parser = new Parser($this);
        
        // Register the standard Tal namespaces
        $this->_parser->registerNamespace( new Parser\Ns\Xml(), Tal::ANY_NAMESPACE );
        $this->_parser->registerNamespace( new Parser\Ns\Tal() );
        $this->_parser->registerNamespace( new Parser\Ns\Metal() );
        
        // Register the standard tales modifiers
        $class = __NAMESPACE__ . '\\Parser\\Tales\\';
        $this->_parser->registerTales( 'path', $class . 'Path' );
        $this->_parser->registerTales( 'not', $class . 'Not' );
        $this->_parser->registerTales( 'exists', $class . 'Exists' );
        $this->_parser->registerTales( 'nocall', $class . 'Nocall' );
        $this->_parser->registerTales( 'string', $class . 'String' );
        $this->_parser->registerTales( 'php', $class . 'Php' );
        
        // Register standard filters
        $this->_parser->registerFilter( new Parser\Filter\Standard() );
    }
    
    /*
     Method: getParser
        Returns the parser object associated with this template, initializing it
        if not yet created.
        
     Returns:
        The <Tal_Parser> object        
    */
    public function getParser()
    {
        if ( !$this->_parser ) {
            $this->_initParser();
        }
        
        return $this->_parser;
    }
    
    public function setParser(Tal\Parser $parser)
    {
        $this->_parser = $parser;
    }
    
    public function getSerializer()
    {
        if (!$this->_serializer) {
            $this->_serializer = new Tal\Parser\Serializer\Php($this);
        }
        
        return $this->_serializer;
    }
    
    public function setSerializer(Tal\Parser\Serializer $serializer)
    {
        $this->_serializer = $serializer;
    }
    
    /*
     Method: _prepare
        Private method to include the compiled template (compiling it if it's not yet compiled)
        
    */
    protected function _prepare( )
    {
        if ( !$this->_prepared ) {
            // Check if we need to generate it again
            if ( Tal::debugging() || !$this->_storage->isCurrent( $this->_tplName ) ) {
                
                $ops = $this->getParser()->build();
                $this->_storage->save( $this->_tplName, $this->getSerializer()->serialize($ops) );
            }
            
            include_once( $this->getScriptPath( $this->_tplName ) );
            $this->_prepared = true;
        }        
    }
    
    /*
     Method: execute
        Runs the template returning the contents or sending the result to stdout
     
     Arguments:
        $return?    - If true the result of the template execution is returned by the
                        function. If false the result is sent directly to the browser.
        
     Returns:
        False if $return is false or the template contents if it's true.
    */
    public function execute( $return = false )
    {
        $content = false;
        
        $this->_prepare();
        
        try{
            
            if ( $return ) {
                ob_start();
            }
            
            $funcName = $this->getScriptIdent();
            $funcName( $this->_context );
                
            if ( $return ) {
                $content = ob_get_clean();
            }
            
        } catch ( Tal\Exception $e ) {
            
            if ( $return ) { 
                ob_end_flush();
            }
            
            throw $e;
            
        }
        
        return $content;
    }

    /*
     Method: __get
        Magic getter which gets a variable from the template context
      
     Arguments:
        $name   - The name of the variable to fetch
        
     Returns:
        The variable contents
        
     See also:
        <Tal_Context->get>
   */
    public function __get( $name )
    {
        $this->_context->get( $name );
    }
    
    /*
     Method: __set
        Magic setter which sets a variable in the template context
      
     Arguments:
        $name   - The name of the variable to set
        $value  - The value to be assigned to the variable
        
     See also:
        <Tal_Context->set>
   */
    public function __set( $name, $value )
    {
        $this->_context->set( $name, $value );
    }
    
}