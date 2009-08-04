<?php

namespace DrSlump\Tal\Parser\Generator\Php\Tales;

use DrSlump\Tal\Parser\Generator\Base;


class Not extends Base\Tales
{
	
    public function evaluate()
    {
		$this->_prefix = true;
		$this->_value = '!';
	}
}