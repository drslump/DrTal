<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;
use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns/Attribute.php';


class AttributesAttribute extends Base\Ns\Attribute
{
    public function beforeElement()
    {
        $value = trim($this->value);
        
        $this->getWriter()
        ->php('$_tal_attributes = array();')->EOL();
        
        while ( $value ) {
            
            // get attribute name
            if ( preg_match( '/^\s*((?:[A-Za-z_]+:)?[A-Za-z_][A-Za-z_-]*)\s+/', $value, $m ) ) {
                $value = substr( $value, strlen($m[0]) );
                
                $varName = '$_tal_attributes[\'' .  $m[1] . '\']';
                
                $attr = $this->element->getAttribute($m[1]);
                $default = $attr ? $attr->getValue() : '';
                
                $this->element->setAttribute(
                    'DrSlump\Tal\Parser\Generator\Php\Ns\Tal\AttributesAttributeSimple',
                    $m[1],
                    '<?php echo $ctx->escape(' . $varName . ');?>',
                    false
                );
                
            } else {
                
                throw new Parser\Exception('No attribute name found');
            
            }
            
            $value = trim( $this->doAlternates( $value, $varName, $default ) );
            
            // Check for a syntax error
            if ( !empty($value) && strpos($value, ';') !== 0 ) {
                throw new Parser\Exception('Synxtax error on tal:attributes expression');
            }
            
            // remove the semi-colon to process a new attribute
            $value = substr($value, 1);
        }
    }
}


// Helper class
class AttributesAttributeSimple extends Base\Ns\Attribute
{
    function __construct( $element, $name, $value, $escape = true )
    {
        parent::__construct( $element, $name, $value, $escape );
        $this->removed = false;
    }    
}
