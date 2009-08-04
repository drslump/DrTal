<?php

namespace DrSlump\Tal\Parser\Generator\Php\Tales;

use DrSlump\Tal\Parser\Generator\Base;


class Exists extends Base\Tales
{
    public function evaluate()
    {
        $this->_value = '$ctx->exists(\'' . trim($this->_exp) . '\')';
        $this->_exp = '';
    }    
}