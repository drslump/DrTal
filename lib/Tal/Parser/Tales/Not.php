<?php

namespace DrSlump\Tal\Parser\Tales;

use DrSlump\Tal\Parser;


class Not extends Parser\Tales
{
	
    public function evaluate()
    {
		$this->_prefix = true;
		$this->_opcodes->code('!');
	}
}