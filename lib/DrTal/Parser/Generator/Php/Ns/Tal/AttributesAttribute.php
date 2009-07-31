<?php

namespace DrTal::Parser::Generator::Php::Ns::Tal;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Generator/Base/Attribute.php';


class AttributesAttribute extends DrTal::Parser::Generator::Base::Attribute
{
    public function beforeElement()
    {
        $value = trim($this->value);
        
        $this->getCodegen()
        ->php('$_tal_attributes = array();')->EOL();
        
        while ( $value ) {
            
            // get attribute name
            if ( preg_match( '/^\s*((?:[A-Za-z_]+:)?[A-Za-z_][A-Za-z_-]*)\s+/', $value, $m ) ) {
                $value = substr( $value, strlen($m[0]) );
                
                $varName = '$_tal_attributes[\'' .  $m[1] . '\']';
                
                $attr = $this->element->getAttribute($m[1]);
                $default = $attr ? $attr->getValue() : '';
                
                $this->element->setAttribute(
                    'DrTal_Parser_Namespace_Tal_AttributesAttribute_Simple',
                    $m[1],
                    '<?php echo $ctx->escape(' . $varName . ');?>',
                    false
                );
                
            } else {
                
                throw new DrTal::Parser::Exception('No attribute name found');
            
            }
            
            $value = trim( $this->doAlternates( $value, $varName, $default ) );
            
            // Check for a syntax error
            if ( !empty($value) && strpos($value, ';') !== 0 ) {
                throw new DrTal::Parser::Exception('Synxtax error on tal:attributes expression');
            }
            
            // remove the semi-colon to process a new attribute
            $value = substr($value, 1);
        }
    }
}

/*
// Helper class
class AttributesAttribute_Simple extends DrTal_Parser_Attribute
{
    function __construct( $element, $name, $value, $escape = true )
    {
        parent::__construct( $element, $name, $value, $escape );
        $this->removed = false;
    }    
}
*/