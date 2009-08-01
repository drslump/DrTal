<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Xml;

use DrSlump\Tal\Parser\Generator\Base;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Attribute.php';

class AnyAttribute extends Base\Attribute
{
    public function beforeElement()
    {
        $this->removed = false;
    }
}
