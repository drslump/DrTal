<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns;

use DrSlump\Tal\Parser\Generator\Base;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal/BlockElement.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal/AttributesAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal/CommentAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal/ConditionAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal/ContentAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal/DefineAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal/OmitTagAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal/OnErrorAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal/RepeatAttribute.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Php/Ns/Tal/ReplaceAttribute.php';

class Tal extends Base\Ns {
    
    public function __construct()
    {
        $ns = 'DrSlump\\Tal\\Parser\\Generator\\Php\\Ns\\Tal\\';
        
        $this->registerElement( 'block', $ns . 'BlockElement' );
        $this->registerAttribute( 'on-error', $ns . 'OnErrorAttribute', \DrSlump\Tal::PRIORITY_MAXIMUM + 1 );
        $this->registerAttribute( 'define', $ns . 'DefineAttribute', \DrSlump\Tal::PRIORITY_MAXIMUM );
        $this->registerAttribute( 'condition', $ns . 'ConditionAttribute', \DrSlump\Tal::PRIORITY_VERYHIGH );
        $this->registerAttribute( 'repeat', $ns . 'RepeatAttribute', \DrSlump\Tal::PRIORITY_HIGH );
        $this->registerAttribute( 'replace', $ns . 'ReplaceAttribute', \DrSlump\Tal::PRIORITY_MEDIUM );
        $this->registerAttribute( 'content', $ns . 'ContentAttribute', \DrSlump\Tal::PRIORITY_MEDIUM );
        $this->registerAttribute( 'attributes', $ns . 'AttributesAttribute', \DrSlump\Tal::PRIORITY_LOW );
        $this->registerAttribute( 'comment', $ns . 'CommentAttribute', \DrSlump\Tal::PRIORITY_VERYLOW ); 
        $this->registerAttribute( 'omit-tag', $ns . 'OmitTagAttribute', \DrSlump\Tal::PRIORITY_VERYLOW );
    }
    
    public function getNamespaceUri()
    {
        return 'http://xml.zope.org/namespaces/tal';
    }

}