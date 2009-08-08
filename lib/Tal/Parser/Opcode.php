<?php

namespace DrSlump\Tal\Parser;

class Opcode {
	protected $_name;
	protected $_args;
	
	public function __construct( $name, $args = array() )
	{
		$this->_name = $name;
		$this->_args = $args;
	}
	
	public function arg($no = 0, $default = null)
	{
		if (isset($this->_args[$no])) {
			return $this->_args[$no];
		} else {
			return $default;
		}
	}
	
	public function args()
	{
		return $this->_args;
	}
	
	public function setArg($idx, $value)
	{
		$this->_args[$idx] = $value;
	}
	
	public function getName()
	{
		return $this->_name;
	}
}


