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
    protected $_found = array();
    
    
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
    
    public function resetRepositories()
    {
        // Invalidate cache
        $this->_found = array();
        parent::resetRepositories();
    }
    
    public function addRepositories($repos)
    {
        $this->_found = array();
        parent::addRepositories($repos);
    }
    
    public function isCurrent( $tplName )
    {
        $tplFile = $this->find($tplName);
        if (!$tplFile) {
            throw new exception( 'Template file not found' );
        }
        
        $phpFile = $this->getScriptPath( $tplName );
        
        return is_readable($phpFile) && filemtime($phpFile) > filemtime($tplFile);
    }
    
    public function load( $tplName )
    {
        $tplFile = $this->find($tplName);
        if (!$tplFile)  {
            throw new Tal\Exception('Template file not found');
        }
        
        return file_get_contents($tplFile);
    }
    
    public function save( $tplName, $tplContents )
    {
        return file_put_contents( $this->getScriptPath($tplName), $tplContents );
    }
    
    public function find( $tplName )
    {
        // First check if we've its path cached
        if ( isset($this->_found[$tplName]) ) {
            return $this->_found[$tplName];
        }
        
        // Mark it as not-found by default
        $this->_found[$tplName] = false;
        
        // Include the current directory in the list of repositories
        $repos = array_merge( array(''), $this->getRepositories() );
        foreach ( $repos as $repo ) {
            if (!empty($repo)) {
                $repo = rtrim( $repo, ' /\\' ) . DIRECTORY_SEPARATOR;
            }
            
            // If found update the cache
            if ( is_readable( $repo . $tplName ) ) {
                $this->_found[$tplName] = $repo . $tplName;
                break;
            }
        }
        
        // Return the path to the template
        return $this->_found[$tplName];
    }
    
    public function getScriptPath( $tplName )
    {
        $tplFile = $this->find($tplName);
        if (!$tplFile) {
            throw new Tal\Exception('Template file not found');
        }
        
        
        // Get the base destination path
        if ( $this->getOption('path') !== NULL ) {
            $path = $this->getOption('path');
        } else if (function_exists('sys_get_temp_dir')) {
            $path = sys_get_temp_dir();
        } else {
            $path = '/tmp';
        }
        
        $path = ltrim( rtrim($path, '/\\ ') ) . DIRECTORY_SEPARATOR;
        
        // Calculate a hash from the template name
        $hash = md5( get_class($this) . '#' . $tplFile );
        
        // Split the hash into several folders if needes
        $maxPartLen = max( $this->getOption('nest-levels')+1, 1 );
        $partLen = min( floor( strlen($hash) / $maxPartLen ), strlen($hash) );
        $parts = str_split( $hash, $partLen );
        $fname = array_pop($parts);
        if ( Tal::debugging() ) {
            $fname .= '-dbg';
        }
        
        // Complete the path
        if ( !empty($parts) ) {
            if ( $this->getOption('path-prefix') !== NULL )
                $path .= $this->getOption('path-prefix');
            else if ( $this->getOption('prefix') !== NULL )
                $path .= $this->getOption('prefix');
                
            $path .= implode( DIRECTORY_SEPARATOR, $parts ) . DIRECTORY_SEPARATOR;
        }
        
        // Create the directories if not existing
        if ( !is_dir($path) ) {
            mkdir( $path, $this->getOption('dmask'), true );
        }
        
        // Apply a prefix to the filename
        if ( $this->getOption('file-prefix') !== NULL )
            $path .= $this->getOption('file-prefix');
        else if ( $this->getOption('prefix') !== NULL )
            $path .= $this->getOption('prefix');
        
        $path .= $fname;
        if ( $this->getOption('extension') !== NULL )
            $path .= '.' . $this->getOption('extension');
            
        return $path;
    }
    
    public function getScriptIdent( $tplName )
    {
        $tplFile = $this->find($tplName);
        if (!$tplFile) {
            throw new Tal\Exception('Template file not found');
        }
        
        return '_' . md5( get_class($this) . '#' . $tplFile );
    }

}
