<?php #$Id$
/*
 File: Tal/Parser/Generator/Base/Ns.php

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

namespace DrSlump\Tal\Parser\Generator\Base;

use DrSlump\Tal;


/*
 Class: Tal::Parser::Generator::Base::Ns
    Abstract class defining a namespace

 See also:
    <Tal::Parser::Generator::Abstract::Ns::Element>, <Tal::Parser::Generator::Abstract::Ns::Attribute>
*/

abstract class Ns {
    
    protected $elements = array();
    protected $attributes = array();
    protected $priorities = array();
    
    
    public function hasElement( $element )
    {
        return isset( $this->elements[$element] ) || isset( $this->elements[Tal::ANY_ELEMENT] );
    }
    
    public function getElement( $element )
    {
        if ( isset($this->elements[$element]) )
            return $this->elements[$element];
        else if ( isset($this->elements[Tal::ANY_ELEMENT]) ) 
            return $this->elements[Tal::ANY_ELEMENT];
        else
            return null;
    }
    
    public function hasAttribute( $attribute )
    {
        return isset( $this->attributes[$attribute] ) || isset( $this->attributes[Tal::ANY_ATTRIBUTE] );
    }
    
    public function getAttribute( $attribute )
    {
        if ( isset($this->attributes[$attribute]) )
            return $this->attributes[$attribute];
        else if ( isset($this->attributes[Tal::ANY_ATTRIBUTE]) )
            return $this->attributes[Tal::ANY_ATTRIBUTE];
        else
            return null;
    }
    
    public function getAttributePriority( $attribute )
    {
        if ( isset($this->priorities[$attribute]) )
            return $this->priorities[$attribute];
        else
            return null;
    }
    
    public function getNamespaceUri()
    {
        $class = str_replace('\\', '/', get_class($this));
        return 'drtal://namespace.uri/' . strtolower($class);
    }
    
    public function getNamespacePrefix()
    {
        static $prefix;
        
        if ( !$prefix ) {
            // get the last word from the class name
            $prefix = preg_replace('/^.*?([a-z]+)$/i', '$1', get_class($this));
            $prefix = strtolower( $prefix );
        }
        
        return $prefix;
    }
    
    public function registerElement( $name, $class )
    {        
        $this->elements[$name] = $class;
        return true;
    }
    
    public function unregisterElement( $name )
    {
        if ( !isset($this->elements[$name]) )
            return false;
        
        unset( $this->elements[$name] );
        return true;
    }

    public function registerAttribute( $name, $class, $priority = Tal::PRIORITY_MEDIUM )
    {
        $this->attributes[$name] = $class;
        $this->priorities[$name] = $priority;
        
        return true;
    }
    
    public function unregisterAttribute( $name )
    {
        if ( !isset($this->attributes[$name]) )
            return false;
        
        unset( $this->attributes[$name] );
        return true;
    }
    
}
