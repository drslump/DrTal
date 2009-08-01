<?php #$Id$
/*
 File: Tal/Parser.php

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

require_once TAL_LIB_DIR . 'Tal/Parser/Exception.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Xml/Exception.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Writer/Php.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Util/StringStream.php';


/*
 Class: Tal::Parser
    This class takes care of parsing a template and calling the registered hooks
    which generate the final compiled template.
    
 Notes:

    XMLReader works always with text in UTF8, so all the text operations
    should take this in consideration.
*/
class Parser
{
    const CONTAINER_NODE    = 'DrTalContainer';    
    
    protected $namespaceDeclaration;
    protected $docTypeDeclaration;
    
    protected $template;
    protected $writer;
    protected $namespaces = array();
    protected $entities = array();
    protected $tales = array();
    protected $filters = array();
    
    /*
     Method: __construct
        Object constructor, instantiates a new parser object
        
     Arguments:
        $template   - a <Tal::Template> object
    */
    public function __construct( Template $template )
    {
        $this->template = $template;
        
        $this->namespaceDeclaration = null;
        $this->docTypeDeclaration = null;
        $this->nsObject = array();
        $this->nsPrefix = array();
        $this->entities = array();
        $this->tales = array();
        $this->filters = array();
    }
    
    /*
     Method: getWriter
        Returns the code generator helper object
     
     Returns:
        A <DrTal_Parser_Writer> object
    */
    public function getWriter()
    {
        return $this->writer;
    }
    
    /*
     Method: setWriter
        Creates a new writer for the given template
    */
    public function setWriter(Template $tpl)
    {
        $this->writer = new Parser\Writer\Php($tpl);
    }
    
    /*
     Method: registerEntity
        Registers a new entity name. Entities can be used as compile-time constants.
        
     Arguments:
        $name       - the entity name
        $value?     - the entity value
        
     Returns:
        true on success, false on failure
    */
    function registerEntity( $name, $value = null )
    {
        $this->entities[ $name ] = is_null($value) ? '' : $value;
        $this->parseDocType = null;
        
        return true;
    }
    
    /*
     Method: unregisterEntity
        Unregisters an entity
        
     Arguments:
        $name       - the entity name
        
     Returns:
        true if the entity existed and was removed, false if not
    */
    function unregisterEntity( $name )
    {
        if ( !isset($this->entities[$name]) ) {
            return false;
        }
        
        unset($this->entities[$name]);        
        $this->parseDocType = null;
        
        return true;    
    }
    
    /*
     Method: registerNamespace
        Registers a new namespace handler.
        
     Arguments:
        $nsObj      - the entity name
        $url?       - the namespace url, if not set the object is queried for it
        $prefix?    - the namespace prefix, if not set the object is queried for it
        
     Returns:
        true on success
        
     Throws:
        - <DrTal_Parser_Exception> if $nsObj does not inherit from <Tal::Parser::Namespace>
    */
    public function registerNamespace( Parser\Generator\Base\Ns $nsObj, $uri = null, $prefix = null )
    {
        if ( !$uri ) {
            $uri = $nsObj->getNamespaceUri();
        }
        
        if ( !$prefix ) {
            $prefix = $nsObj->getNamespacePrefix();
        }
        
        $this->namespaces[$uri] = array(
            'prefix'    => $prefix,
            'object'    => $nsObj,
        );
        
        // unset the namespaces cache
        $this->parseNamespaces = null;
        
        return true;
    }
    
    /*
     Method: unregisterNamespace
        Unregisters a namespace
        
     Arguments:
        $url        - the namesapce url
        
     Returns:
        true if the namespace existed and was removed, false if not
    */
    public function unregisterNamespace( $uri )
    {
        if ( !isset($this->namespace[$uri]) ) {
            return false;
        }
        
        unset( $this->namespace[$uri] );
        
        // unset the namespaces cache
        $this->parseNamespaces = null;
        
        return true;
    }
    
    /*
     Method: registerFilter
        Registers a new content filter
        
     Arguments:
        $name       - the desired filter name
        $filterObj  - the <DrTal_Parser_Filter> object to register
        
     Returns:
        true on success
        
     Throws:
        - <Tal_Parser_Exception> if $filterObj does not inherit from <DrTal_Parser_Filter>
    */
    public function registerFilter( $name, $filterObj )
    {
        if ( !($filterObj instanceof Parser\Filter) ) {
            throw new Parser\Exception( get_class($filterObj) . ' must inherit from DrSlump\\Tal\\Parser\\Filter' );
        }
        
        $this->filters[ $name ] = $filterObj;
        
        return true;
    }
    
    /*
     Method: unregisterFilter
        Unregisters a filter
        
     Arguments:
        $name       - the filter name used when registered
        
     Returns:
        true if the filter existed and was removed, false if not
    */    
    public function unregisterFilter( $name )
    {
        if ( !isset($this->filters[$name]) ) {
            return false;
        }
        
        unset($this->filters[$name]);
        return true;
    }
    
    /*
     Method: registerTales
        Registers a new tales hadnler
        
     Arguments:
        $name       - the desired tales name
        $callback   - a function or method to handle this tales expression
        
     Returns:
        true on success
        
     Throws:
        - <Tal_Parser_Exception> if $callback is not actually callable
    */    
    public function registerTales( $name, $callback )
    {
        if ( !is_callable($callback) ) {
            throw new Parser\Exception( "The callback supplied for the '$name' tales handler is not valid" );
        }
        
        $this->tales[$name] = $callback;
    }
    
    /*
     Method: unregisterTales
        Unregisters a tales handler
        
     Arguments:
        $name       - the tales name to unregister
        
     Returns:
        true if the tales handler existed and was removed, false if not
    */
    public function unregisterTales( $name )
    {
        if ( !isset($this->tales[$name]) ) {
            return false;
        }
        
        
        unset($this->tales[$name]);
        return true;
    }

    /*
     Method: getTales
        Returns the tales handler associated to the supplied modifier
        
     Arguments:
        $modifier   - the tales handler id
        
     Returns:
        A callable variable if found or false if not
    */
    public function getTales( $modifier )
    {
        if ( !isset($this->tales[$modifier]) ) {
            return false;
        }
        
        return $this->tales[$modifier];
    }
    

    /*
     Method: build
        Generates a compiled template
     
     Arguments:
        $tplObj     - a <DrTal_Template> object
     
     Throws:
        - <Tal_Parser_Exception> if there was an error generating the template
    */
    public function build()
    {
        $startTime = microtime(true);
        
        // Fetch the template
        $tpl = $this->template->getSource();
        
        // Initialize the code generator
        $this->setWriter($this->template);
        
        $this->getWriter()
        ->comment('Generated by DrTal on ' . gmdate('d/m/Y H:i:s'))->EOL()
        ->php('function ' . $this->template->getScriptIdent() . '( $ctx ) {')->EOL();
        
        if ( Tal::debugging() ) {
            if (function_exists('gzcompress')) {
                $payload = gzcompress($tpl, 9);
            } else {
                $payload = $tpl;
            }
            
            $this->getWriter()
            ->php('$ctx->setDebugTemplate(\'' . "\n    " . implode("\n    ", str_split(base64_encode($payload), 76)) . '\');')->EOL();
        }
        
        $lineNo = 1;
        
        // Fetch original xml declaration and remove
        if ( ($pos = strpos( $tpl, '<?xml' )) !== false ) {
            if ( !trim(substr($tpl, 0, $pos)) ) {
                $pos = strpos( $tpl, '?>', $pos ) + 2;
                $xmldecl = substr( $tpl, 0, $pos );
                $lineNo += $this->countLines($xmldecl);
                $this->getWriter()->xml( $xmldecl );
                $tpl = substr( $tpl, $pos );
            }
        }
        
        // Fetch original template doctype and remove
        if ( ($pos = strpos( $tpl, '<!DOCTYPE ' )) !== false ) {
            if ( !trim(substr($tpl, 0, $pos)) ) {
                $pos = strpos( $tpl, '>', $pos ) + 1;
                $doctype = substr($tpl, 0, $pos);
                $lineNo += $this->countLines($doctype);
                $this->getWriter()->xml( $doctype );
                $tpl = substr( $tpl, $pos );                
            }
        }
        
        if ( Tal::debugging() ) {
            foreach ( $this->namespaces as $uri => $ns ) {
                $this->getWriter()->php('$ctx->setDebugNamespace(\'' . $uri . '\', \'' . $ns['prefix'] . '\');')->EOL();
            }
        }
        
        // Build the xml by wrapping the template with entities and namespaces
        $xml = $this->getDocTypeDeclaration();
        $xml .= '<' . self::CONTAINER_NODE . ' ' . $this->getNamespaceDeclaration() . '>';
        
        $lineNo -= $this->countLines($xml);
        
        $xml .= $tpl;
        $xml .= '</' . self::CONTAINER_NODE . '>';        
        
        
        //echo "<pre>" . htmlentities($xml) . "</pre>";        
        //echo str_repeat('<hr/>', 3);            
            
        // Load the xml (non validating, no DTD mode)
        $reader = new \XMLReader();
        $reader->XML( $xml, 0 );
        
        // We want to capture the parsing errors
        $oldLibXmlErrorMode = libxml_use_internal_errors(true);
        libxml_clear_errors();
        
        try {
            // Parse the template
            $this->parse( $reader, $lineNo, true );
            
            // End the xml parsing
            $reader->close();
            
            // Get any parsing errors
            $errors = libxml_get_errors();
            libxml_clear_errors();
            
            // restore the original error handling mode 
            libxml_use_internal_errors( $oldLibXmlErrorMode );
            
        } catch ( Exception $e ) {
            
            $reader->close();
            
            libxml_clear_errors();
            libxml_use_internal_errors( $oldLibXmlErrorMode );
            
            $this->getWriter()->abort();
            
            throw $e;
        }

        // If XML errors where found generate a suitable exception
        if ( !empty($errors) ) {
            
            $this->getWriter()->abort();
            
            $exc = new Parser\Xml\Exception( 'Error parsing template' );
            $exc->setXml( $this->tplString );
            
            foreach ($errors as $error) {
                
                if ( $error->level === LIBXML_ERR_WARNING ) {
                    $exc->addXmlWarning( $error->line-1, $error->column, $error->code, $error->message );
                } else {
                    $exc->addXmlError( $error->line-1, $error->column, $error->code, $error->message );
                }
                
            }
            
            var_dump( $exc );
            
            throw $exc;
        }
        
        // Finish the template function
        $this->getWriter()
        ->EOL(true)
        ->php('}')->EOL();
            
        // Write down the time spent generating the error
        $this->getWriter()->append('<?php /* Generation took: ' . (microtime(true)-$startTime) . ' seconds */ ?>');
        
        // Finally just save the file to persist all the code
        $this->getWriter()->save();
    }
    
    protected function parse( $reader, $lineNo, $haltOnWarning = false )
    {
        $stack = array();
        
        // Start processing the template
        do {
            
            // Go to the next item in the document
            if ( !@$reader->read() ) {
                break;
            }
            
            $lineNo += $this->countLines($reader->value);
            
            switch ( $reader->nodeType ) {
                case XMLReader::ELEMENT:
                    
                    if ( $reader->name === self::CONTAINER_NODE ) {
                        continue;
                    }
                    
                    $ns = $reader->namespaceURI;
                    $prefix = $reader->prefix;
                    $name = $reader->localName;
                    $isEmpty = $reader->isEmptyElement;
                    
                    $debug = '<' . $reader->name;
                    if ( DrTal::debugging() ) {
                        $this->getWriter()->php('$ctx->setDebugHint(\'' . $reader->name . '\');')->EOL();
                    }
                    
                    // Find the element handler
                    $nsObj = $this->getNamespace($ns);
                    if ( $nsObj->hasElement($name) ) {
                        $class = $nsObj->getElement($name);
                        if ( !class_exists($class) ) {
                            throw new Parser_Exception( "Element handler class '$class' not found" );
                        }
                        $elmObj = new $class( $this, $nsObj, $reader->name, $isEmpty );
                    } else {
                        throw new Parser_Exception( "'($ns) $name' has no handler" );
                    }
                    
                    $attrs = array();
                    if ( $reader->hasAttributes ) {
                        
                        // First Get all the attributes information
                        while ($reader->moveToNextAttribute()) {
                            
                            // Get attribute namespace object
                            $nsObj = $this->getNamespace( $reader->namespaceURI ? $reader->namespaceURI : $ns );                            
                            
                            if ( !$nsObj->hasAttribute( $reader->localName ) ) {
                                throw new Parser\Exception("Attribute '{$reader->localName}' has no handler");
                            }
                             
                            $attrs[] = array(
                                'class'     => $nsObj->getAttribute( $reader->localName ),
                                'priority'  => $nsObj->getAttributePriority( $reader->localName ),
                                'name'      => $reader->name,
                                'value'     => $reader->value
                            );
                            
                        }
                        
                        // Sort them based on their priority
                        $attrs = Tal::sortByPriority( $attrs );
                        
                        // Attach the attributes to the element
                        foreach ( $attrs as $attr ) {
                            if ( !class_exists($attr['class']) ) {
                                throw new Parser\Exception( "Attribute handler class '{$attr['class']}' not found" );
                            }
                            
                            $elmObj->setAttribute( $attr['class'], $attr['name'], $attr['value']  );                            
                        }                        
                    }      
                    
                    // Now let's run their before element handler
                    $elmObj->runBeforeElement();
                    // Process the start element handler
                    $elmObj->start();
                        
                        
                    if ( !$elmObj->getEmpty() ) {
                        $elmObj->runBeforeContent();
                    }
                    
                    if ( !$isEmpty ) {
                        
                        // Store to be used in the end element
                        array_push( $stack, $elmObj );
                        
                    } else {
                        
                        if ( !$elmObj->getEmpty() ) {
                        
                            // Let's run the attributes after content handler
                            $elmObj->runAfterContent();
                            
                            // Now launch the element end handler
                            $elmObj->end();
                        }
                        
                        // Let's run the attributes after element handler
                        $elmObj->runAfterElement();                        
                    }
                    
                break;
            
                case XMLReader::END_ELEMENT:
                    
                    if ( $reader->name === self::CONTAINER_NODE ) {
                        continue;
                    }
                    
                    $elmObj = array_pop($stack);
                        
                    if ( !$elmObj->getEmpty() ) {
                        // Let's run the attributes after content handler
                        $elmObj->runAfterContent();
                        
                        $elmObj->end();
                    }
                    
                    // Let's run the attributes after element handler
                    $elmObj->runAfterElement();
                    
                break;
            
                case XMLReader::TEXT:
                    
                    $data = $reader->value;
                    
                    foreach ( $this->filters as $filter ) {
                        $data = $filter->text( $data );
                    }
                    
                    if ( $data !== null && $data !== false ) {
                        $this->getWriter()->xml( $data );
                    }
                break;
            
                case XMLReader::PI:
                    
                    $data = $reader->value;
                    
                    foreach ( $this->filters as $filter ) {
                        $data = $filter->pi( $reader->name, $data );
                    }
                    
                    if ( $data !== null && $data !== false ) {
                        $this->getWriter()->xml( '<?' . $reader->name . ' ' . $data . '?>' );
                    }
                    
                break;
                
                case XMLReader::CDATA:
                    
                    $data = $reader->value;
                    
                    foreach ( $this->filters as $filter ) {
                        $data = $filter->cdata( $data );
                    }
                    
                    if ( $data !== null && $data !== false ) {
                        $this->getWriter()->xml( '<![CDATA[' . $data . ']]>' );
                    }
                    
                break;
                
                case XMLReader::WHITESPACE:
                case XMLReader::SIGNIFICANT_WHITESPACE:
                    
                    $data = $reader->value;
                    
                    foreach ( $this->filters as $filter ) {
                        $data = $filter->whitespace( $data );
                    }
                    
                    if ( $data !== null && $data !== false ) {
                        $this->getWriter()->xml( $data );
                    }
                    
                break;
                    
                case XMLReader::COMMENT:
                    
                    $data = $reader->value;
                    
                    foreach ( $this->filters as $filter ) {
                        $data = $filter->comment( $data );
                    }
                    
                    if ( $data !== null && $data !== false ) {
                        $this->getWriter()->xml( '<!-- ' . $data . ' -->' );
                    }
                    
                break;
                
                case XMLReader::ENTITY_REF:
                    
                    if ( is_array($this->entities[$reader->localName]) ) {
                        $val = call_user_func( $this->entities[$reader->localName], $reader->localName );
                    } else {
                        $val = $this->entities[$reader->localName];
                    }
                    
                    if ( $data === null || $data === false ) {
                        // do nothing
                    } else if ( is_scalar($val) ) {
                        $this->getWriter()->xml( $val );
                    } else {
                        $this->getWriter()->xml( '&' . $reader->localName . ';' );
                    }
                break;
            
                case XMLReader::ENTITY:
                case XMLReader::END_ENTITY:
                case XMLReader::DOC_TYPE:
                    
                    // Skip
                    
                break;
            
                default:
                
                    if ( DrTal::debugging() )
                        $this->dumpNode($reader, 'UNKNOWN (' . $reader->nodeType . ')');
            }              
            
            // Exit if there has been a warning and we should halt
        } while ( !($haltOnWarning && libxml_get_last_error()) );
            
    }
    
    protected function getDocTypeDeclaration()
    {
        if ( !$this->docTypeDeclaration ) {
            $this->docTypeDeclaration = '<!DOCTYPE ' . self::CONTAINER_NODE . ' [';
            foreach ( $this->entities as $name=>$value ) {
                $this->docTypeDeclaration .= "<!ENTITY $name \"\">";
            }
            $this->docTypeDeclaration .= ']>' . PHP_EOL;
        }
        
        return $this->docTypeDeclaration;
    }
    
    protected function getNamespaceDeclaration()
    {
        if ( !$this->namespaceDeclaration ) {
            $this->namespaceDeclaration = '';
            foreach( $this->namespaces as $uri=>$arr ) {
                if ( $arr['prefix'] ) {
                    $prefix = 'xmlns:' . $arr['prefix'];
                } else {
                    $prefix = 'xmlns';
                }
                
                $this->namespaceDeclaration .= $prefix . '="' . $uri . '" ';
            }
        }
        
        return $this->namespaceDeclaration;
    }        

    protected function getNamespace( $uri )
    {
        if ( isset($this->namespaces[$uri]) )
            return $this->namespaces[$uri]['object'];
        else if ( isset($this->namespaces[DrTal::ANY_NAMESPACE]) )
            return $this->namespaces[DrTal::ANY_NAMESPACE]['object'];
        
        throw new DrTal_Parser_Exception( 'Namespace "' . $uri . '" not registered and no default namespace to use' );
    }
    
    protected function countLines( $text )
    {
        return count( preg_split('/\r\n|\n|\r/', $text) )-1;
    }
    
    protected function dumpNode($o, $type = 'Unknown')
    {
        echo "<strong>$type</strong><pre>";
        echo "attributeCount = " . $o->attributeCount . "\n";
        echo "baseURI = " . $o->baseURI . "\n";
        echo "depth = " . $o->depth . "\n";
        echo "hasAttributes = " . ( $o->hasAttributes ? 'TRUE' : 'FALSE' ) . "\n";
        echo "hasValue = " . ( $o->hasValue ? 'TRUE' : 'FALSE' ) . "\n";
        echo "isDefault = " . ( $o->isDefault ? 'TRUE' : 'FALSE' ) . "\n";
        echo "isEmptyElement = " . ( @$o->isEmptyElement ? 'TRUE' : 'FALSE' ) . "\n";
        echo "localName = " . $o->localName . "\n";
        echo "name = " . $o->name . "\n";
        echo "namespaceURI = " . $o->namespaceURI . "\n";
        echo "nodeType = " . $o->nodeType . ' - ' . $node_types[$o->nodeType] . "\n";
        echo "prefix = " . $o->prefix . "\n";
        echo "value = " . $o->value . "\n";
        echo "xmlLang = " . $o->xmlLang . "\n";
        echo "</pre>";
    }
}
