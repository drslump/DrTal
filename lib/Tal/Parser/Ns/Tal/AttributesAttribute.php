<?php

namespace DrSlump\Tal\Parser\Ns\Tal;

use DrSlump\Tal\Parser;
use DrSlump\Tal\Parser\OpcodeList;
use DrSlump\Tal\Parser\Ns;

class AttributesAttribute extends Parser\Attribute
{
    public function beforeElement()
    {
        $value = trim($this->getValue());
        
        $this->getProgram()
        ->code('$_attributes = array();');
        
        $elem = $this->getElement();
        
        while ( $value ) {
            
            $hasDefault = false;
            
            // Get the attribute name
            if ( preg_match( '/^((?:[A-Za-z_]+:)?[A-Za-z_][A-Za-z_-]*)\s+/', $value, $m ) ) {
                // Reduce the expression
                $value = substr( $value, strlen($m[0]) );
                
                $attrName = $m[1];
                $varName = "\$_attributes['" .  $attrName . "']";
                
                // Get default value
                $attr = $elem->getAttribute($attrName);
                if ($attr) {
                    $hasDefault = true;
                    $this->getProgram()
                    ->assign('$_default', $attr->getValue());
                }
                
                $statement = OpcodeList::factory('context', 'write', array('$_attributes[\'' . $attrName . '\']', true));
                $attr = new Ns\Xml\AnyAttribute($this->getElement(), $attrName, $statement);
                $elem->setAttribute($attr);
                
            } else {
                
                throw new Parser\Exception('No attribute name found');
            
            }
            
            // Evaluate tales expression assigned to the attribute name
            $value = $this->doAlternates( $value, $varName, $hasDefault ? '$_default' : false );
            
            // Check for a syntax error
            if ( !empty($value) && strpos($value, ';') !== 0 ) {
                throw new Parser\Exception('Synxtax error on tal:attributes expression');
            }
            
            // remove the semi-colon to process a new attribute
            $value = substr($value, 1);
        }
    }
}
