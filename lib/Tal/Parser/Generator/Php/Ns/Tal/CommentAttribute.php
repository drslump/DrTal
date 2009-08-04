<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Tal;

use DrSlump\Tal\Parser\Generator\Base;

class CommentAttribute extends Base\Ns\Attribute
{
    public function beforeElement()
    {
        $this->getWriter()->comment( $value );
    }
}