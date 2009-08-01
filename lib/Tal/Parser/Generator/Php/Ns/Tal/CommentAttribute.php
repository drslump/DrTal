<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;

require_once TAL_LIB_DIR . 'Tal/Parser/Generator/Base/Ns/Attribute.php';


class CommentAttribute extends Base\Ns\Attribute
{
    public function beforeElement()
    {
        $this->getWriter()->comment( $value );
    }
}