<?php

namespace DrSlump\Tal\Parser\Ns\Xml;

use DrSlump\Tal\Parser;


class AnyAttribute extends Parser\Attribute
{
    public function __construct( Parser\Element $element, $name, $value)
    {
        parent::__construct($element, $name, $value);
        $this->isRemoved(false);
    }
}
