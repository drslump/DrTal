<?php

namespace DrSlump\Tal\Parser;

class OpcodeList extends \SplDoublyLinkedList {

	public function clear()
	{		
		// Empty the list of nodes
		while (count($this)) {
			$this->pop();
		}
	}
	
	public function appendList(OpcodeList $list)
	{
		foreach( $list as $op ) {
			$this->push($op);
		}
		
		return $this;
	}
	
	public function __call($name, $args) {
		
        $name = strtoupper($name);
		
		if (strpos($name, 'END') === 0) {			
			$name = 'END';
		}
			
		$this->push( new Opcode($name, $args) );
		
        return $this;
    }
	
	static public function factory($name /* $arg1, $arg2, $arg3 ... */)
	{
		// Get the arguments as an array
		$args = func_get_args();
		// Shift the name from the list
		array_shift($args);
				
		$list = new self();
		//return call_user_func(array($list, '__call'), $name, $args);
		return call_user_func_array(array($list, $name), $args);
	}
	
	/*
	  APC <= 3.1.2 has a bug with serving a call with __callStatic comming
	  from a different file - http://pecl.php.net/bugs/bug.php?id=16083
	  
	  Until a new APC version is released with this bug fixed we cannot use
	  this magic method and have to use instead a "factory" method.
	*/
	static public function __callStatic($name, $args)
	{
		array_unshift($args, $name);
		return call_user_func_array(array(self, 'factory'), $args);
	}
}