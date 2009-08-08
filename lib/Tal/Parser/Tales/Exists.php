<?php

namespace DrSlump\Tal\Parser\Tales;

use DrSlump\Tal\Parser;


class Exists extends Parser\Tales
{
    public function evaluate()
    {
        //$this->_value = '$ctx->exists(\'' . trim($this->_exp) . '\')';
        $this->_opcodes->context('exists', array($this->_exp));
        $this->_exp = '';
    }    
}