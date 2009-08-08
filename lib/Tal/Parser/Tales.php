<?php #$Id$
/*
 File: Tal/Parser/Generator/Base/Tales.php

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
    
    copyright (c) 2008 Iv‡n -DrSlump- Montes <http://pollinimini.net>
*/

namespace DrSlump\Tal\Parser;

use DrSlump\Tal;

/*
 Class: Tal::Parser::Generator::Base::Tales
    Abstract class defining a tales modifier

 See also:
    <Tal::Parser::Generator::Php::Tales::Path>, <Tal::Parser::Generator::Php::Tales::String>
*/
abstract class Tales {
    
    protected $_parser;
    protected $_exp;
    protected $_opcodes;
    protected $_prefix = false;
    
    public function __construct( Tal\Parser $parser, $exp )
    {
        $this->_parser = $parser;
        $this->_exp = $exp;
        $this->_opcodes = new Tal\Parser\OpcodeList();
    }
    
    public function getExpression()
    {
        return trim($this->_exp);
    }
    
    public function getOpcodes()
    {
        return $this->_opcodes;
    }
    
    public function isFinished()
    {
        return trim($this->_exp) === '';
    }
    
    public function isPrefix()
    {
        return $this->_prefix;
    }
    
    abstract public function evaluate();
}
