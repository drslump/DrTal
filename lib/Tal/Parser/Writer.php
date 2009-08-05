<?php #$Id$
/*
 File: Tal/Parser/Writer.php

    DrTal - A TAL template engine for PHP
    
 License:

    The GNU General Public License version 3 (GPLv3)
    
    This file is part of DrTal.

    DrTal is free software; you can redistribute it and/or modify it under the
    terms of the GNU General Public License as published by the Free Software
    Foundation; either version 2 of the License, or (at your option) any later
    version.
    
    DrTal is distributed in the hope that it will be useful, but WITHOUT ANY
    WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
    FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
    details.
    
    You should have received a copy of the GNU General Public License along with 
    DrTal; if not, write to the Free Software Foundation, Inc., 51 Franklin
    Street, Fifth Floor, Boston, MA 02110-1301, USA
    
    See bundled license.txt or check <http://www.gnu.org/copyleft/gpl.html>

 Copyright:
    
    copyright (c) 2008 Iván -DrSlump- Montes <http://pollinimini.net>
*/

namespace DrSlump\Tal\Parser;

use DrSlump\Tal;


class WriterNode {
    
    protected $_mode;
    protected $_children;
    
    public function __construct($mode, $args = array())
    {
        $this->_mode = $mode;
        $this->_children = array();
        
        foreach ($args as $k=>$v) {
            $this->$k = $v;
        }
    }
    
    public function getMode()
    {
        return $this->_mode;
    }
    
    public function addChild($node)
    {
        $this->_children[] = $node;
    }
    
    public function getChildren()
    {
        return $this->_children;
    }
    
    public function hasChildren()
    {
        return count($this->_children) > 0;
    }
}


abstract class Writer
{    
    protected $_template;
    protected $_node;
    protected $_stack;
    
    public function __construct(Tal\Template $template)
    {
        $this->_template = $template;
        
        $this->reset();
    }
    
    abstract public function build();

    public function reset()
    {
        //$this->_node = new WriterNode('ROOT');
        $this->_node = new \ArrayObject();
        $this->_node->mode = 'ROOT';
        $this->_node->children = array();
        $this->_stack = array();
    }

    public function abort()
    {
        $this->reset();
    }


    protected function _push($mode, $args = array())
    {
        array_push($this->_stack, $this->_node);
        $this->_node = $this->_append($mode, $args);
    }
    
    protected function _pop()
    {
        $this->_node = array_pop($this->_stack);
    }
    
    protected function _append($mode, $args)
    {
        $node = new \ArrayObject();
        $node->mode = $mode;
        $node->children = array();
        foreach ($args as $k=>$v) {
            $node->$k = $v;
        }
        
        $this->_node->children[] = $node;

        return $node;
    }


    /*
     Since PHP does not allow to use some reserved words as method names (if,
     else, echo ...) we use the __call magic method to support those names.
    */
    public function __call($func, $args)
    {        
        // Grow the arguments array for optional arguments
        $args = array_pad($args, 10, null);
             
        $func = strtoupper($func);
        switch ($func) {
            case 'XML':
            case 'CODE':
            case 'ECHO':
            case 'VAR':
            case 'PATH':
            case 'COMMENT':
                $this->_append($func, array('content' => $args[0]));
            break;
            case 'IF':
                $this->_push($func, array('condition' => $args[0]));
            break;
            case 'ELSE':
                $this->_append($func, array('condition' => $args[0]));
            break;
            case 'ITERATE':
                $this->_push($func, array('iterator' => $args[0]));
            break;
            case 'CAPTURE':
                $this->_push($func, array('variable' => $args[0]));
            break;
            case 'TRY':
                $this->_push($func);
            break;
            case 'CATCH':
                $this->_append($func, array('exception'=>$args[0], 'var'=>$args[1]));
            break;
            case 'THROW';
                $this->_append($func, array('exception' => $args[0], 'args' => $args[1]));
            break;
            case 'TEMPLATE':
                $this->_push($func, array('ident' => $args[0]));
            break;
            case 'CONTEXT':
                $this->_append($func, array('method'=>$args[0], 'args'=>$args[1]));
            break;
            default:
                if (strpos($func, 'END') === 0) {
                    $this->_pop();
                    return $this;
                } else {
                    trigger_error('Function ' . $func . ' not available', E_USER_ERROR);    
                }
        }
        
        return $this;        
    }
}
