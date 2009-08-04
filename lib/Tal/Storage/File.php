<?php #$Id$
/*
 File: Tal/Storage/File.php

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

use DrSlump\Tal;
use DrSlump\Tal\Storage;

require_once TAL_LIB_DIR . 'Tal/Storage.php';


/*
 Class: Tal::Storage::File
    Default storage adapter which works with physical files

 Options:
    dmask       - directory mask to use when creating a new one (default: 0755)
    path        - base path where compiled templates will be stored (default: system's temp dir or /tmp)
    prefix      - prefix to use for directories and files (default: no prefix)
    path-prefix - prefix for the 1st level directory (default: the value of prefix)
    file-prefix - prefix for the file (default: the value of prefix)
    extension   - file extension (default: no extension)
    nest-levels - number of subdirectories (default: 0)
        
 Extends:
    <Tal::Storage>
*/
class File extends Storage
{
    protected $cached = array();
    
    
    public function __construct( $options = array(), $repositories = array() )
    {
        $defaults = array(
            'dmask'         => 0755,
            'path'          => null,
            'prefix'        => '',
            'path-prefix'   => '',
            'file-prefix'   => '',
            'extension'     => '',
            'nest-levels'   => 0            
        );
        $options = array_merge( $defaults, $options );
        
        parent::__construct( $options, $repositories );
    }
    
    public function setRepositories( $repo )
    {
        // Invalidate cache
        $this->cached = array();
        parent::setRepositories();
    }
    
    public function find( $fname, $tplClass )
    {
        if ( isset($this->cached[$fname]) )
            return true;
        
        $repos = array_merge( array(''), $this->repositories );        
        foreach ( $repos as $repo ) {
            if ( $repo ) {
                $repo = rtrim( $repo, ' /\\' ) . DIRECTORY_SEPARATOR;
            }
            
            if ( is_readable( $repo . $fname ) ) {
                $this->cached[$fname] = $repo . $fname;
                return new $tplClass( $this, $fname );
            }
        }
        
        return false;
    }
    
    public function isCurrent( $tplName )
    {
        if ( !$this->cached[$tplName] ) {
            throw new exception( '.....' );
        }
        
        $phpFile = $this->getScriptStream( $tplName );
        $tplFile = $this->cached[$tplName];
        
        return is_readable($phpFile) && filemtime($phpFile) > filemtime($tplFile);
    }
    
    public function load( $tplName )
    {
        if ( !$this->cached[$tplName] ) {
            throw new exception( '.....' );
        }
        
        return file_get_contents( $this->cached[$tplName] );
    }
    
    public function getScriptPath( $tplName )
    {
        if ( !$this->cached[$tplName] ) {
            throw new exception( '.....' );
        }
        
        $md5 = md5( get_class($this) . '#' . $this->cached[$tplName] );
        $maxPartLen = max( $this->options['nest-levels']+1, 1 );
        $partLen = min( floor( strlen($md5) / $maxPartLen ), strlen($md5) );
        $parts = str_split( $md5, $partLen );
        $fname = array_pop($parts);
        if ( Tal::debugging() ) {
            $fname .= '-dbg';
        }
        
        if ( isset($this->options['path']) ) {
            $path = $this->options['path'];
        } else if (function_exists('sys_get_temp_dir')) {
            $path = sys_get_temp_dir();
        } else {
            $path = '/tmp';
        }
        
        $path = ltrim( rtrim($path, '/\\ ') ) . DIRECTORY_SEPARATOR;
        
        if ( !empty($parts) ) {
            if ( isset($this->options['path-prefix']) )
                $path .= $this->options['path-prefix'];
            else if ( isset($this->options['prefix']) )
                $path .= $this->options['prefix'];
                
            $path .= implode( DIRECTORY_SEPARATOR, $parts ) . DIRECTORY_SEPARATOR;
        }
        
        if ( !is_dir($path) ) {
            mkdir( $path, isset($this->options['dmask']) ? $this->options['dmask'] : 0755, true );
        }
        
        if ( isset($this->options['file-prefix']) )
            $path .= $this->options['file-prefix'];
        else if ( isset($this->options['prefix']) )
            $path .= $this->options['prefix'];
            
        $path .= $fname;
        
        if ( isset($this->options['extension']) )
            $path .= '.' . $this->options['extension'];
            
        return $path;
    }
    
    public function getScriptIdent( $tplName )
    {
        if ( !$this->cached[$tplName] ) {
            throw new exception( '.....' );
        }
        
        return '_' . md5( get_class($this) . '#' . $this->cached[$tplName] );
    }

}
