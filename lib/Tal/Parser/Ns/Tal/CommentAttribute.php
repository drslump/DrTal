<?php

namespace DrSlump\Tal\Parser\Ns\Tal;

use DrSlump\Tal\Parser;

class CommentAttribute extends Parser\Attribute
{
    public function beforeElement()
    {
        $this->getProgram()->comment( $value );
    }
}