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
use DrSlump\Tal\Parser;

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
    
    protected $_template;
    protected $_program;
    protected $_namespaces = array();
    protected $_entities = array();
    protected $_tales = array();
    protected $_filters = array();
    
    /*
     Method: __construct
        Object constructor, instantiates a new parser object
        
     Arguments:
        $template   - a <Tal::Template> object
    */
    public function __construct( Template $template )
    {
        $this->_namespaces = array();
        
        $this->_tales = array();
        $this->_filters = new Parser\Util\PriorityArrayObject();
        $this->_entities = array();
        
        $this->_template = $template;
        $this->_program = new Parser\OpcodeList();
    }
    
    /*
     Method: getProgram
        Returns the code generator helper object
     
     Returns:
        A <Tal_Parser_OpcodeList> object
    */
    public function getProgram()
    {
        return $this->_program;
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
    public function registerEntity( $name, $value = null )
    {
        $this->_entities[ $name ] = is_null($value) ? '' : $value;
        
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
    public function unregisterEntity( $name )
    {
        if ( !isset($this->_entities[$name]) ) {
            return false;
        }
        
        unset($this->_entities[$name]);        
        
        return true;    
    }
    
    /*
     Method: getEntity
        Returns the value of an entity
        
     Arguments:
        $name   - The name of the entity
        
     Returns:
        The entity value or null if not found
    */
    public function getEntity($name)
    {
        if (isset($this->_entities[$name])) {
            return $this->_entities[$name];
        }
        
        return null;
    }
    
    public function getEntities()
    {
        return new \ArrayIterator($this->_entities);
    }

    /*
     Method: clearEntities
        Resets the list of registered entities
        
    */
    public function clearEntities()
    {
        foreach ($this->_entities as $name=>$value) {
            $this->unregisterEntity($name);
        }
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
        - <Tal_Parser_Exception> if $nsObj does not inherit from <Tal::Parser::Namespace>
    */
    public function registerNamespace( Parser\Ns $nsObj, $uri = null, $prefix = null )
    {
        if ( !$prefix && $uri !== Tal::ANY_NAMESPACE ) {
            $prefix = $nsObj->getNamespacePrefix();
        }
        
        if ( !$uri || $uri === Tal::ANY_NAMESPACE ) {
            $uri = $nsObj->getNamespaceUri();
        }
        
        $this->_namespaces[$uri] = array(
            'prefix'    => $prefix,
            'object'    => $nsObj,
        );
        
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
        if ( !isset($this->_namespace[$uri]) ) {
            return false;
        }
        
        unset( $this->_namespace[$uri] );
        
        return true;
    }
    
    /*
     Method: getNamespace
        Returns an already registered namepace object
        
     Arguments:
        $uri    - the namespace uri
    */
    public function getNamespace( $uri )
    {
        if ( isset($this->_namespaces[$uri]) )
            return $this->_namespaces[$uri]['object'];
        else if ( isset($this->_namespaces[Tal::ANY_NAMESPACE]) )
            return $this->_namespaces[Tal::ANY_NAMESPACE]['object'];
        
        throw new Tal\Parser\Exception( 'Namespace "' . $uri . '" not registered and no default namespace to use' );
    }
    
    /*
     Method: clearNamespaces
        Unregisters all the registered namespaces
     
    */
    public function clearNamespaces()
    {
        foreach ($this->_namespaces as $uri=>$obj) {
            $this->unregisterNamespace($uri);
        }
    }
    
    public function getNamespaces()
    {
        return new \ArrayIterator($this->_namespaces);
    }

    /*
     Method: registerFilter
        Registers a new content filter
        
     Arguments:
        $filterObj  - the <DrTal_Parser_Filter> object to register
        $priority   - (Optional) The filter priority
        
     Returns:
        true on success
        
     Throws:
        - <Tal_Parser_Exception> if $filterObj does not inherit from <DrTal_Parser_Filter>
    */
    public function registerFilter( $filterObj, $priority = Tal::PRIORITY_NORMAL )
    {
        if ( !($filterObj instanceof Parser\Filter) ) {
            throw new Parser\Exception( get_class($filterObj) . ' must inherit from DrSlump\\Tal\\Parser\\Filter' );
        }
        
        $this->_filters->insert( $filterObj, $priority );
        return true;
    }
    
    /*
     Method: unregisterFilter
        Unregisters a filter
        
     Arguments:
        $objOrClass       - the filter instance or its class
    */    
    public function unregisterFilter( $objOrClass )
    {
        $oldCount = count($this->_filters);
        
        foreach($this->_filters as $k=>$obj) {
            if (is_string($objOrClass) && is_a($obj, $objOrClass)) {
                unset($k);
            } else if ($obj === $objOrClass) {
                unset($k);
            }
        }
        
        return count($this->_filters) !== $oldCount;
    }
    
    public function getFilters()
    {
        return $this->_filters;
    }
    
    /*
     Method: registerTales
        Registers a new tales hadnler
        
     Arguments:
        $name       - the desired tales name
        $handler    - a <Tal_Parser_Generator_Base_Tales> descendant class name
        
     Returns:
        true on success
    */    
    public function registerTales( $name, $handler )
    {       
        $this->tales[$name] = $handler;
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
    public function getTales( $modifier = null )
    {
        if ( NULL === $modifier ) {
            return \ArrayIterator($this->_tales);    
        }
        
        if ( !isset($this->tales[$modifier]) ) {
            return false;
        }
        
        return $this->tales[$modifier];
    }
    

    /*
     Method: build
        Generates a compiled template
     
     Arguments:
        $tplObj     - a <Tal_Template> object
     
     Throws:
        - <Tal_Parser_Exception> if there was an error generating the template
    */
    public function build()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        // Fetch the template contents
        $tpl = $this->_template->getSource();
        
        // Initialize the opcodes list
        $this->getProgram()->clear();
        
        // Initialize the template code
        $this->getProgram()
        ->comment('Generated by DrTal on ' . gmdate('d/m/Y H:i:s'))
        ->template($this->_template->getScriptIdent());
        
        /* We are now loading the original template code on exceptions
        if ( Tal::debugging() ) {
            $this->getProgram()->context('dbgTemplate', array( explode("\n", $tpl) ));
        }
        */
        
        $lineNo = 0;
        
        // Fetch original xml declaration and remove
        if ( ($pos = strpos( $tpl, '<?xml' )) !== false ) {
            if ( !trim(substr($tpl, 0, $pos)) ) {
                $pos = strpos( $tpl, '?>', $pos ) + 2;
                $xmldecl = substr( $tpl, 0, $pos );
                $lineNo += $this->countLines($xmldecl);
                $tpl = substr( $tpl, $pos );
                // Write the original declaration to the compiled template
                $this->getProgram()->xml( $xmldecl );
            }
        }
        
        // Fetch original template doctype and remove
        if ( ($pos = strpos( $tpl, '<!DOCTYPE ' )) !== false ) {
            if ( !trim(substr($tpl, 0, $pos)) ) {
                $pos = strpos( $tpl, '>', $pos ) + 1;
                $doctype = substr($tpl, 0, $pos);
                $lineNo += $this->countLines($doctype);
                $tpl = substr( $tpl, $pos );                
                // Write the original to the compiled template
                $this->getProgram()->xml( $doctype );
            }
        }
        
        
        // Build the xml by wrapping the template with entities and namespaces
        $xml = '<!DOCTYPE ' . self::CONTAINER_NODE . " [\n\t";        
        // Define registerd entities in the doctype
        foreach ( $this->getEntities() as $name=>$value ) {
            $xml .= "<!ENTITY $name \"\">";
        }
        $xml .= "\n]>\n";
        
        $xml .= '<' . self::CONTAINER_NODE . ' ';
        foreach( $this->getNamespaces() as $uri=>$arr ) {
            if ( Tal::debugging() ) {
                //$this->getProgram()->code('$ctx->setDebugNamespace(\'' . $uri . '\', \'' . $arr['prefix'] . '\');');
            }
            
            $xml .= 'xmlns';
            if ( $arr['prefix'] ) {
                $xml .= ':' . $arr['prefix'];
            }            
            $xml .= '="' . $uri . '" ';
        }        
        $xml .= '>';
        
        
        // Adjust relative line number
        //! Not needed since we count the lines manually???
        //$lineNo -= $this->countLines($xml);
        
        $xml .= $tpl;
        $xml .= '</' . self::CONTAINER_NODE . '>';        
        
        
        echo "<pre>" . htmlspecialchars($xml) . "</pre><hr/>";
            
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
            
            throw $e;
        }

        // If XML errors where found generate a suitable exception
        if ( !empty($errors) ) {
            
            $exc = new Parser\Xml\Exception( 'Error parsing template' );
            $exc->setXml( $xml );
            
            foreach ($errors as $error) {
                
                if ( $error->level === LIBXML_ERR_WARNING ) {
                    $exc->addWarning( $error->line-1, $error->column, $error->code, $error->message );
                } else {
                    $exc->addError( $error->line-1, $error->column, $error->code, $error->message );
                }
                
            }
            
            echo '<pre>' . htmlspecialchars($xml) . '</pre>';
            var_dump( $exc );
            
            throw $exc;
        }
        
        
        // Finish the template function
        $this->getProgram()->endTemplate();
            
        // Write down the time spent generating the template
        $this->getProgram()
        ->append()
            ->comment(
                "Generation took: " . (microtime(true)-$startTime) . " seconds\n" .
                "Memory consumption: " . number_format((memory_get_usage()-$startMemory)/1024, 2) . "Kb"
            )
        ->endAppend();
        
        // Finally just save the file to persist all the code
        return $this->getProgram();
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
                case \XMLReader::ELEMENT:
                    // Skip the injected containing node
                    if ( $reader->name === self::CONTAINER_NODE ) {
                        continue;
                    }
                    
                    $ns = $reader->namespaceURI;
                    $prefix = $reader->prefix;
                    $name = $reader->localName;
                    $isEmpty = $reader->isEmptyElement;
                    
                    $debug = '<' . $reader->name;
                    if ( Tal::debugging() ) {
                        $this->getProgram()->context('dbgHint', array($lineNo, $reader->name));
                    }
                    
                    // Find the element handler
                    $nsObj = $this->getNamespace($ns);
                    if ( $nsObj->hasElement($name) ) {
                        $class = $nsObj->getElement($name);
                        if ( !class_exists($class) ) {
                            throw new Parser\Exception( "Element handler class '$class' not found" );
                        }
                        $elmObj = new $class( $this, $nsObj, $reader->name, $isEmpty );
                    } else {
                        throw new Parser\Exception( "'($ns) $name' has no handler" );
                    }
                    
                    // SplPriorityQueue will take care of sorting the attributes
                    $attrs = new \SplPriorityQueue();
                    if ( $reader->hasAttributes ) {
                        
                        // First Get all the attributes information
                        while ($reader->moveToNextAttribute()) {
                            
                            $lineNo += $this->countLines($reader->value);
                            
                            // Get attribute namespace object
                            $nsObj = $this->getNamespace( $reader->namespaceURI ? $reader->namespaceURI : $ns );                            
                            
                            if ( !$nsObj->hasAttribute( $reader->localName ) ) {
                                throw new Parser\Exception("Attribute '{$reader->localName}' has no handler");
                            }
                             
                            $class = $nsObj->getAttributeClass($reader->localName);
                            if ( !class_exists($class) ) {
                                throw new Parser\Exception( "Attribute handler class '$class' not found" );
                            }                            
                            
                            // Insert in the queue to sort it
                            $attrs->insert(
                                new $class($elmObj, $reader->name, $reader->value),
                                $nsObj->getAttributePriority($reader->localName)
                            );
                        }
                        
                        // Attach the attributes to the element
                        foreach ( $attrs as $attr ) {
                            $elmObj->setAttribute( $attr );
                        }                        
                    }      
                    
                    // If actions are performed in the element then add a hint in the generated code
                    if (Tal::debugging()) {
                        $hintAttrs = '';
                        foreach ($elmObj->getAttributes() as $attr) {
                            if (!($attr instanceof Parser\Ns\Xml\AnyAttribute)) {
                                $pfx = $attr->getPrefix() ? $attr->getPrefix() . ':' : '';
                                $hintAttrs .= $pfx . $attr->getName() . '="' . $attr->getValue() . '" ';
                            }
                        }
                        
                        if ($hintAttrs || !($elmObj instanceof Parser\Ns\Xml\AnyElement)) {
                            $pfx = $elmObj->getPrefix() ? $elmObj->getPrefix() . ':' : '';
                            $this->getProgram()->comment('<' . $pfx . $elmObj->getName() . ' ' . trim($hintAttrs));
                        }
                    }
                    
                    // Now let's run their before element handler
                    $elmObj->beforeElement();
                    // Process the start element handler
                    $elmObj->open();
                        
                    // Check if the element has content
                    if ( !$elmObj->isEmpty() ) {
                        $elmObj->beforeContent();
                    }
                    
                    if ( !$isEmpty ) {
                        
                        // Store to be used in the end element
                        $stack[] = $elmObj;
                        
                    } else {
                        
                        if ( !$elmObj->isEmpty() ) {
                        
                            // Let's run the attributes after content handler
                            $elmObj->afterContent();
                            
                            // Now launch the element end handler
                            $elmObj->close();
                        }
                        
                        // Let's run the attributes after element handler
                        $elmObj->afterElement();                        
                    }
                    
                break;
            
                case \XMLReader::END_ELEMENT:
                    // Skip injected containing node
                    if ( $reader->name === self::CONTAINER_NODE ) {
                        continue;
                    }
                    
                    $elmObj = array_pop($stack);
                    
                    // Check if the element has content
                    if ( !$elmObj->isEmpty() ) {
                        // Let's run the attributes after content handler
                        $elmObj->afterContent();
                        
                        $elmObj->close();
                    }
                    
                    // Let's run the after element handler
                    $elmObj->afterElement();
                    
                break;
            
                case \XMLReader::TEXT:
                    $opcodes = Parser\OpcodeList::factory('xml', $reader->value);
                    
                    foreach ( $this->getFilters() as $filter ) {
                        $opcodes = $filter->text( $opcodes );
                    }
                    
                    $this->getProgram()->appendList($opcodes);
                break;
            
                case \XMLReader::CDATA:
                    $opcodes = Parser\OpcodeList::factory('xml', '<![CDATA[' . $reader->value . ']]>');
                    
                    foreach ( $this->getFilters() as $filter ) {
                        $opcodes = $filter->cdata( $opcodes );
                    }
                    
                    $this->getProgram()->appendList($opcodes);
                break;
                
                case \XMLReader::WHITESPACE:
                case \XMLReader::SIGNIFICANT_WHITESPACE:
                    $opcodes = Parser\OpcodeList::factory('xml', $reader->value);
                    
                    foreach ( $this->getFilters() as $filter ) {
                        $opcodes = $filter->whitespace( $opcodes );
                    }
                    
                    $this->getProgram()->appendList($opcodes);
                break;
                    
                case \XMLReader::PI:
                    $opcodes = Parser\OpcodeList::factory('xml', '<?' . $reader->value . '?>');
                    
                    foreach ( $this->getFilters() as $filter ) {
                        $opcodes = $filter->pi( $reader->name, $opcodes );
                    }
                    
                    $this->getProgram()->appendList($opcodes);
                    
                break;
                
                case \XMLReader::COMMENT:
                    $opcodes = Parser\OpcodeList::factory('xml', '<!-- ' . $reader->value . ' -->');
                    
                    foreach ( $this->getFilters() as $filter ) {
                        $opcodes = $filter->comment( $opcodes );
                    }
                    
                    $this->getProgram()->appendList($opcodes);
                break;
                
                case \XMLReader::ENTITY_REF:
                    
                    if (isset($this->_entities[$reader->localName])) {
                        // Check if it's a callback or a value
                        if ( is_callable($this->_entities[$reader->localName]) ) {
                            $val = call_user_func( $this->_entities[$reader->localName], $reader->localName );
                        } else {
                            $val = $this->_entities[$reader->localName];
                        }
                        
                        // Inspect the value returned
                        if ( is_string($val) || is_numeric($val) ) {
                            $this->getProgram()->xml( $val );
                        } else if ( $val instanceof Parser\OpcodeList ) {
                            $this->getProgram()->appendList($val);
                        }                        
                        
                    } else {
                        // By default just output the entity as is
                        $this->getProgram()->xml('&' . $reader->localName . ';');
                    }
                   
                break;
            
                case \XMLReader::ENTITY:
                case \XMLReader::END_ENTITY:
                case \XMLReader::DOC_TYPE:
                    
                    // Skip
                    
                break;
            
                default:
                
                    if ( Tal::debugging() )
                        $this->dumpNode($reader, 'UNKNOWN (' . $reader->nodeType . ')');
            }              
            
            // Exit if there has been a warning and we should halt
        } while ( !($haltOnWarning && libxml_get_last_error()) );
            
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
