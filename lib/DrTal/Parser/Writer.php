<?php #$Id$
/*
 File: DrTal/Parser/Writer.php

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

namespace DrTal::Parser;

abstract class Writer
{
    const EOL = "\n";
    const INDENT = "  ";
    
    const MODE_XML = 1;
    const MODE_PHP = 2;
    
    const FLOW_IF = 'if';
    const FLOW_FOR = 'for';
    const FLOW_FOREACH = 'foreach';
    const FLOW_WHILE = 'while';
    const FLOW_TRY = 'try';
    const FLOW_CAPTURE = 'capture';
    
    protected $mode = self::MODE_XML;
    protected $flows = array();
    protected $captures = array();
    protected $modes = array();
    protected $content = '';
    protected $indent = '';
    protected $appends = array();

    public function __construct( DrTal::Template $tpl )
    {
        $this->template = $tpl;
        $this->mode = self::MODE_XML;

        $this->flows = array();
        $this->captures = array();
        $this->content = '';
        
        $this->fp = fopen( $tpl->getScriptStream(), 'w' );
        if ( !$this->fp ) {
            throw new DrTal::Parser::Exception( 'Unable to create template script' );
        }
    }
    
    public function __destruct()
    {
        $this->abort();
    }
    
    public function save()
    {
        foreach ($this->appends as $append) {
            $this->xml($append);
        }
        
        $this->closePhp();
        
        fclose($this->fp);
        $this->fp = null;
    }

    public function getTemplate()
    {
        return $this->template;
    }
    
    public function abort()
    {
        $this->content = '';
        $this->captures = array();
        $this->flows = array();
        if ( $this->fp ) {
            fclose($this->fp);
            unlink($this->template->getScriptStream());
            $this->fp = null;
        }        
    }
    
    public function debugTales( $tales, $expr )
    {
        if ( DrTal::debugging() )
            $this->comment( $tales . '="' . str_replace( '"', '\\"', $expr ) . '"' );
            
        return $this;
    }
    
    public function getCapture($raw = false)
    {
        if ($raw) {
            return $this->content;
        }
        
        $content = '';
        
        $mode = array_pop($this->modes);
        if ($mode === self::MODE_PHP) {
            $content .= '<?php ';
        }
        array_push( $this->modes, $mode );
        
        $content .= $this->content;
        
        if ($this->mode === self::MODE_PHP) {
            $content .= ' ?>';
        }
        
        return $content;
    }
    
    public function append( $code )
    {
        $this->appends[] = $code;
        return $this;
    }
    
    public function indent()
    {
        $this->indent++;
        return $this;
    }
    
    public function unindent()
    {
        $this->indent = max( 0, $this->indent-1 );
        return $this;
    }
    
    public function capture()
    {        
        $this->enterFlow( self::FLOW_CAPTURE );
        
        array_push( $this->captures, $this->content );
        array_push( $this->modes, $this->mode );
        $this->content = '';
        
        return $this;
    }
    
    public function endCapture( $flush = false )
    {
        $content = array_pop( $this->captures );
        $mode = array_pop( $this->modes );
        
        if ( $flush ) {
            $this->content = $content . $this->content;
        } else {
            $this->content = $content;
            $this->mode = $mode;
        }
        
        $this->exitFlow( self::FLOW_CAPTURE );
        
        return $this;
    }
    

    public function EOL( $forcePhp = false )
    {
        if ($forcePhp) {
            $this->openPhp();
        }
        
        $this->write( self::EOL . str_repeat( self::INDENT, $this->indent ) );
        return $this;
    }

    public function xml( $xml )
    {
        $this->closePhp();
        
        // Work around to xml declaration do not conflict with PHP short tags
        if ( empty($this->captures) && !ltrim($this->content) && preg_match('/^([^<]*)<\?xml\s/', $xml, $m) ) {
            $this->write( $m[1] );
            $this->write( '<?php echo \'<?xml \';?>' );
            $xml = substr( $xml, strlen($m[0]) );
        }
        
        $this->write( $xml );
        return $this;
    }
    
    protected function write( $str )
    {
        if ( empty($this->captures) ) {
            fwrite( $this->fp, $str );
        } else {
            $this->content .= $str;
        }
        return $this;
    }
    
    protected function writeLn( $str )
    {
        $this->write( $str );
        $this->eol();
        return $this;
    }
    
    public function openPhp()
    {
        if ( $this->mode === self::MODE_XML ) {
            $this->write( '<?php ' );
            $this->mode = self::MODE_PHP;
        }
        return $this;
    }
    
    public function closePhp()
    {
        if ( $this->mode === self::MODE_PHP ) {
            $this->write( '?>' );
            $this->mode = self::MODE_XML;
        }
        return $this;
    }
    
    public function __call( $method, $args )
    {
        $method = strtolower($method);
        
        switch ( $method ) {
            case 'if':
            case 'else':
            case 'elseif':
            case 'while':
            case 'for':
            case 'foreach':
            case 'call':
            case 'echo':
            case 'echopath':
            case 'comment':
            case 'try':
            case 'catch':
            case 'throw':
            case 'rethrow':
            case 'php':
                $this->openPhp();
                call_user_func_array( array($this, 'do' . $method), $args );
                return $this;
            
            case 'endif':
            case 'endfor':
            case 'endforeach':            
            case 'endwhile':
            case 'endtry':
                call_user_func_array( array($this, 'end'), array( substr($method, 3) ) );
                return $this;
        }
        
        throw new ::Exception('Method ' . $method . ' not defined');
    }
    
    protected function enterFlow( $flow )
    {
        array_push( $this->flows, $flow );
    }
    
    protected function exitFlow( $untilFlow = null )
    {
        if ( !$untilFlow ) {
            $current = array_pop( $this->flows );
        } else {
            do {
                $current = array_pop( $this->flows );
            } while ( $current && $current !== $untilFlow );
        }
        
        if (!$current) {
            throw new Exception( 'No more flows to exit!' );
        }
    }
    
    protected function currentFlow()
    {
        $cnt = count($this->flows);        
        return $cnt ? $this->flows[ $cnt-1 ] : false;
    }    
    
    
    protected function doIf( $condition )
    {
        $this->enterFlow( self::FLOW_IF );
        
        $this->eol()
            ->write( 'if (' . $condition . '):' )
            ->indent()
            ->eol();
    }
    
    protected function doElse()
    {
        /// Close all constructs until an if is found
        while ( $this->currentFlow() !== self::FLOW_IF ) {
            $this->exitFlow();
        }
        
        $this->unindent()
            ->eol()
            ->writeLn( 'else:' )
            ->indent();
    }
    
    protected function doElseIf( $condition )
    {
        /// Close all constructs until an if is found
        while ( $this->currentFlow() !== self::FLOW_IF ) {
            $this->exitFlow();
        }
        
        $this->unindent()
            ->eol()
            ->writeLn( 'else if (' . $condition . '):' )
            ->indent();
    }
        
    protected function doWhile( $condition )
    {
        $this->enterFlow( self::FLOW_WHILE );
        
        $this->eol()
            ->writeLn( 'while (' . $condition . '):' )
            ->indent();
    }
    
    protected function doFor( $init, $condition, $step )
    {
        $this->enterFlow( self::FLOW_FOR );
        
        $this->eol()
            ->writeLn( 'for ( ' . $init . '; ' . $condition . '; ' . $step . ' ):' )
            ->indent();
    }
    
    protected function doForeach( $def )
    {
        $this->enterFlow( self::FLOW_FOREACH );
        
        $this->eol()
            ->writeLn( 'foreach ( ' . $def . ' ):' )
            ->indent();
    }
    
    protected function doTry()
    {
        $this->enterFlow( self::FLOW_TRY );
        
        $this->eol()
            ->writeLn( 'try {')
            ->indent();
    }
    
    protected function doCatch( $exception )
    {
        /// Close all constructs until a try is found
        while ( $this->currentFlow() !== self::FLOW_TRY ) {
            $this->exitFlow();
        }
        
        $this->unindent()
            ->eol()
            ->writeLn( '} catch (' . $exception . ' $e) {' )
            ->indent();
    }
    
    protected function doThrow( $exception, $msg = '')
    {
        $this->writeLn('throw new ' . $exception . '(\'' . addslashes($msg) . '\');');
    }
    
    protected function doReThrow()
    {
        $this->writeln('throw $e;');
    }
    
    public function end( $flow = null )
    {
        if ( !$flow ) {
            $flow = $this->currentFlow();
        }
        
        if ( $flow === self::FLOW_CAPTURE ) {
            $this->endCapture();
        } else if ( $flow === self::FLOW_TRY ) {
            
            $this->exitFlow( $flow );
            $this->openPhp()
                ->unindent()
                ->eol()
                ->writeLn( '}' );
            
        } else {
            $this->exitFlow( $flow );
            
            $this->openPhp()
                ->unindent()
                ->eol()
                ->writeLn( 'end' . ucfirst($flow) . ';' );
        }
        
        return $this;
    }

    
    protected function doCall( $func, $args = array(), $return = null )
    {
        $code = '';
        if ( $return ) {
            $code .= $return . ' = ';
        }
        
        if (!is_array($args)) {
            $args = array($args);
        }
        
        // TODO: Process argument types (strings)
        $code .=  $func . '(' . implode(', ', $args) . ');';
        
        $this->writeLn( $code );
        
        return $this;
    }
    
    protected function doComment( $comment )
    {
        $this->write( '/* ' . str_replace( '*/', '* /', $comment ) . ' */' );
    }
    
    protected function doEcho( $str )
    {
        $this->write( 'echo ' . htmlspecialchars( $str ) . ';' );
    }
    
    protected function doEchoPath( $path )
    {
        $this->write( 'echo $ctx->escape(\'' . $path . '\');' );
    }
    
    protected function doPhp( $code )
    {
        $this->write( $code );
    }
}