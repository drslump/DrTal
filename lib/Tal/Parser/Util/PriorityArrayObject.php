<?php

namespace DrSlump\Tal\Parser\Util;

/*
	Extends ArrayObject to keep it always sorted by a priority attribute of
	each value in the array
	
	Note: Only objects can be stored in this iterator
*/
class PriorityArrayObject extends \ArrayObject {
    
	
	/**
	 * We will keep here all the heap objects to associate them with priorities
	 *
	 * @var SplObjectStorage
	 */
    protected $_priorities;
    
    public function __construct()
    {
        $this->_priorities = new \SplObjectStorage();
    }

    public function insert($value, $priority = 0)
    {
		// Attach this object to the registry of priorities
        $this->_priorities->attach($value, $priority);
		
		// Insert the value in the array
        parent::append($value);
		
		// Sort the array
		$this->uksort(array($this, 'compare'));		
    }
	
    public function compare($a, $b) {
		// Higher priorities go at the top
        return $this->_priorities[ $this[$a] ] > $this->_priorities[$this[$b]] ? -1 : 1;
    }
}
