<?php #$Id$
/*
 File: Tal/Storage/String.php

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

namespace DrSlump\Tal\Storage;

require_once TAL_LIB_DIR . 'Tal/Storage/File.php';


/*
 Class: Tal::Storage::String
    Storage adapter for strings. It will store the compiled template on disk.

 Options:
    this adapter does not have any options on its own. See <Tal::Storage::File>
        
 Extends:
    <Tal::Storage::File> « <Tal::Storage>
*/
class String extends File
{
    protected $cached = array();
        
    public function find( $tplName, $tplClass )
    {
        $hash = 'String:' . md5($tplName);
        $this->cached[ $hash ] = $tplName;
        
        return new $tplClass( $this, $hash );
    }
    
    public function isCurrent( $tplName )
    {
        return false;
    }
    
    public function load( $tplName )
    {
        return $this->cached[$tplName];
    }
}
