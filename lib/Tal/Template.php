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
    
    protected $parser;
    protected $context;
    protected $finder;
    protected $tplName;
    protected $prepared = false;
    
    
    /*
     Constructor: __construct
        Class constructor to initialize the relationship with an storage
     
     Arguments:
        $storage    - A <Tal_Storage> related to this template
        $tplName    - The template name as understood by the given storage
     */
    public function __construct( Storage $finder, $tplName )
    {
        $this->finder = $finder;
        $this->tplName = $tplName;
        $this->context = new Context($this);
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
        $tpl = $this->finder->load( $this->tplName );
        
        return $tpl;
    }
    
    /*
     Method: getScriptStream
        Returns the compiled template stream path
        
     Returns:
        the compiled template stream path
        
     See also:
        <Tal_Storage->getScriptStream>        
    */
    public function getScriptStream()
    {
        return $this->finder->getScriptStream( $this->tplName );
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
        return $this->finder->getScriptIdent( $this->tplName );        
    }
    
    /*
     Method: initParser
        Private method to setup the parser.
        
     Note:
        Extend this method if your need to configure the parser for your needs.
        
        (start code)
        @php
        protected function initParser() {
            parent::initParser();
            $this->parser->registerNamespace( new MyCustomNamespace() );
            $this->parser->registerEntity( 'version', Tal::VERSION_SIGNATURE );
        }
        (end code)
    */
    protected function initParser()    
    {
        require_once TAL_LIB_DIR . 'Tal/Parser.php';
        require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Tales.php';
        require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Xml.php';
        require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal.php';
        require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Metal.php';
        require_once TAL_LIB_DIR . 'Tal/Parser/Filter/Php.php';
        
        $this->parser = new Parser( $this );
        
        $this->parser->registerNamespace( new Tal\Parser\Generator\Php\Ns\Xml(), Tal::ANY_NAMESPACE );
        $this->parser->registerNamespace( new Tal\Parser\Generator\Php\Ns\Tal() );
        $this->parser->registerNamespace( new Tal\Parser\Generator\Php\Ns\Metal() );
        
        
        $class = 'DrSlump\\Tal\\Parser\\Generator\\Php\\Tales::';
        
        $this->parser->registerTales( 'path', $class . 'path' );
        $this->parser->registerTales( 'not', $class . 'not' );
        $this->parser->registerTales( 'exists', $class . 'exists' );
        $this->parser->registerTales( 'nocall', $class . 'nocall' );
        $this->parser->registerTales( 'string', $class . 'string' );
        $this->parser->registerTales( 'php', $class . 'php' );
        
        $this->parser->registerFilter( 'default', new Tal\Parser\Filter\Php() );            
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
        if ( !$this->parser ) {
            $this->initParser();
        }
        
        return $this->parser;
    }
    
    /*
     Method: prepare
        Private method to include the compiled template (compiling it if it's not yet compiled)
        
    */
    protected function prepare( )
    {
        if ( !$this->prepared ) {
            if ( Tal::debugging() || !$this->finder->isCurrent( $this->tplName ) ) {
                
                $this->getParser()->build();
            }
            
            include_once( $this->getScriptStream( $this->tplName ) );
        }
        
        $this->prepared = true;
    }
    
    /*
     Method: execute
        Runs the template returning the contents or sending the result to stdout
     
     Arguments:
        $display?   - If false the result of the template execution is returned by the
                        function. If true the result is sent directly to the browser.
        
     Returns:
        False if $display is true or the template contents if it's false.
    */
    public function execute( $display = true )
    {
        $content = false;
        
        $this->prepare();
        
        try{
            
            if ( !$display ) {
                ob_start();
            }
            
            $funcName = $this->getScriptIdent();
            $funcName( $this->context );
                
            if ( !$display ) {
                $content = ob_get_contents();
                ob_end_clean();
            }
            
        } catch ( DrTal_Exception $e ) {
            
            if ( !$display ) { 
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
        <DrTal_Context->get>
   */
    public function __get( $name )
    {
        $this->context->get( $name );
    }
    
    /*
     Method: __set
        Magic setter which sets a variable in the template context
      
     Arguments:
        $name   - The name of the variable to set
        $value  - The value to be assigned to the variable
        
     See also:
        <DrTal_Context->set>
   */
    public function __set( $name, $value )
    {
        $this->context->set( $name, $value );
    }
    
}