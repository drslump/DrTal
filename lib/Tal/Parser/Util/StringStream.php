<?php

namespace DrSlump\Tal\Parser\Util;

class StringStream {
    
    static protected $registered;
    static protected $refs = array();
    
    protected $string;
    protected $pos = 0;
    
    
    static public function register()
    {
        if ( !self::$registered ) {
            if ( !stream_wrapper_register( 'DrTalString', 'DrTal_Parser_Util_StringStream' ) ) {
                throw new Parser\Exception( 'Unable to register the DrTalString stream wrapper' );
            }
            
            self::$registered = true;
        }
    }
    
    
    /*
        Method: setVariable
        Static method to create a new variable by reference
     
        Returns:
            True on success False on failure      
    */
    static public function setVariable( $name, &$var )
    {
        if ( is_string($var) ) {
            self::register();
            self::$refs[$name] =& $var;
            return true;
        }
        
        return false;
    }
    
    static public function getVariable( $name )
    {
        if ( isset(self::$refs[$name]) ) {
            return self::$refs[$name];
        }
        
        return false;
    }
    
    static public function removeVariable( $name )
    {
        if ( isset(self::$refs[$name]) ) {
            unset(self::$regs[$name]);
            return true;
        }
        
        return false;
    }
    
    
    public function stream_open( $path, $mode, $options, &$openedPath )
    {
        $url = parse_url( $path );
        if ( empty($url['host']) ) {
            return false;
        }
        
        // If not exists create a new one
        if ( !isset(self::$refs[ $url['host'] ]) ) {
            self::$refs[ $url['host'] ] = '';
        }
        
        $this->string =& self::$refs[ $url['host'] ];         
        $this->pos = 0;
        
        return true;
    }
    
    public function stream_read( $count )
    {
        $read = substr( $this->string, $this->pos, $count );
        $this->pos += strlen($read);
        return $read;
    }
    
    public function stream_write( $data )
    {
        $len = strlen($data);
        
        $this->string = substr( $this->string, 0, $this->pos ) .
                        $data .
                        substr( $this->string, 0, $this->pos + $len );
                        
        $this->pos += $len;
        
        return $len;
    }
    
    public function stream_tell()
    {
        return $this->pos;
    }
    
    public function stream_eof()
    {
        return $this->pos >= strlen($this->string);
    }
    
    public function stream_seek( $offset, $whence )
    {
        switch ($whence) {
            case SEEK_SET: $pos = $offset; break;
            case SEEK_CUR: $pos = $this->pos + $offset; break;
            case SEEK_END: $pos = strlen($this->string) + $offset; break;
            default: return false;
        }
        
        if ( $pos < 0 || $pos > strlen($this->string) ) {
            return false;
        }
        
        $this->pos = $pos;
        return false;
    }
    
    public function stream_stat() {
        $attrs = array(
            'dev'   => -1,
            'ino'   => -1,
            'mode'  => 0666,
            'nlink' => 1,
            'uid'   => 0,
            'gid'   => 0, 
            'rdev'  => 0,
            'size'  => strlen($this->string),
            'atime' => time(),
            'mtime' => time(),
            'ctime' => time(),
            'blksize'=> 4096,
            'blocks'=> 8
        );
        
        $i = 0;
        $ret = array();
        foreach ($attrs as $k=>$v) {
            $ret[$i++] = $v;
            $ret[$k] = $v;
        }
        
        return $ret;
    }    
    
}
