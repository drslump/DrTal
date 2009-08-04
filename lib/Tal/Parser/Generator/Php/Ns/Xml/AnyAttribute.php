<?php

namespace DrSlump\Tal\Parser\Generator\Php\Ns\Xml;

use DrSlump\Tal\Parser\Generator\Base;


class AnyAttribute extends Base\Ns\Attribute
{
    public function beforeElement()
    {
        $this->removed = false;
    }
}
