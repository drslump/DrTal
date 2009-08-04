<?php

namespace DrSlump\Tal\Parser\Xml;

use DrSlump\Tal\Parser;

class Exception extends Parser\Exception
{    
    protected $xml;
    protected $xmlErrors = array();
    
    public function setXml( $xml )
    {
        $this->xml = $xml;
    }
    
    public function getXml()
    {
        return $this->xml;
    }
    
    public function addXmlWarning( $ln, $col, $code, $message )
    {
        $this->xmlErrors[] = array(
            'level' => 'warning',
            'ln'    => $ln,
            'col'   => $col,
            'code'  => $code,
            'msg'   => $message
        );
    }
    
    public function addXmlError( $ln, $col, $code, $message )
    {
        $this->xmlErrors[] = array(
            'level' => 'error',
            'ln'    => $ln,
            'col'   => $col,
            'code'  => $code,
            'msg'   => $message
        );        
    }
    
    public function getXmlErrors()
    {
        return $this->xmlErrors;
    }
}

        /*    
            $levels = array(
                LIBXML_ERR_WARNING  => 'Warning', 
                LIBXML_ERR_ERROR    => 'Error',
                LIBXML_ERR_FATAL    => 'Fatal',
            );
            
            // First aggregate on line+column numbers
            $aggregated = array();
            foreach ($errors as $error) {
                $key = ($error->line-1) . '-' . $error->column;                
                
                $err = array(
                    'level' => $error->level,
                    'code'  => $error->code,
                    'msg'   => $error->message,
                );
                
                
                if ( isset( $aggregated[$key] ) )
                    $aggregated[$key][] = $err;
                else
                    $aggregated[$key] = array($err);
            }
            
            // Convert the template to an array of lines            
            $lines = preg_split( '/\r\n|\n|\r/', $this->tplString );
            $numLinesToShow = 5;
            
            foreach ( $aggregated as $key=>$errors ) {
                
                list( $ln, $col ) = explode('-', $key);
                
                $prevLn = max( 0, $ln - floor($numLinesToShow/2) );
                $postLn = min( count($lines), $ln + ceil($numLinesToShow/2) );
                
                echo '
                <style>
                    thead td {
                        font-family: sans-serif;
                        font-size: 120%;
                        background: black;
                        color: white;
                    }
                    th{ text-align: right }
                    td{
                        font-family: Monospace;
                        padding-left: .5em;
                    }
                    .current{
                        background: #ecc;
                    }
                    .current th{background:#caa;}
                    .current td {
                        font-weight: bold;
                        font-size: 120%;
                    }
                    .even{background:#eee}
                    .even th{ background: #ccc }
                    .odd{background:#ddd}
                    .odd th { background: #bbb }
                    .marker {
                        background: #666;
                        color: #FF3300;
                        font-size: 120%;
                        font-weight: bold;
                        height: 10px;
                        line-height: 10px;
                        overflow: hidden;
                    }
                    .marker span {
                        font-size: 80%;
                        color: #ddd;
                    }
                </style>';
                
                echo '
                <table width="100%" cellspacing="0" cellpadding="2">
                <thead>';
                foreach ( $errors as $err ) {
                    echo '
                        <tr>
                            <td colspan="2">
                                ' .  "{$levels[$err['level']]} #{$err['code']}: <em>" . htmlentities($err['msg']) . '</em>
                            </td>
                        </tr>';
                }
                echo '
                </thead>
                <tbody>';
                
                for ( $i=$prevLn; $i<$postLn; $i++ ) {
                    echo '<tr class="' . ($i==$ln ? 'current' : ($i%2 ? 'odd' : 'even')) . '">';
                    echo '<th>' . $i . '</th>';
                    echo '<td>' . htmlentities($lines[$i]) . '</td>';
                    echo '</tr>';
                    if ( $i == $ln ) {
                        echo '<tr class="marker">';
                        echo '<th>&nbsp;</th>';
                        echo '<td>' . str_repeat('-', $col) . '^ <span>(Col: ' . $col . ')</span></td>';
                        echo '</tr>';
                    }
                }
                echo '
                </tbody>
                </table>';
                
                break;
                
                $line = $lines[$error->line-1];
                $trimmed = ltrim($line);
                
                $error->column = $error->column + ( strlen($line)-strlen($trimmed) );
                
                if ( $error->code == 68 ) {
                    $error->column = strrpos( substr($line, 0, $error->column), '<' );
                }
                
                
                echo "<h3>" . $levels[$error->level] . " #{$error->code} at line {$error->line} column {$error->column}</h3>";
                echo "<pre>";
                echo htmlentities( rtrim($lines[$error->line-1]) . PHP_EOL );
                echo str_repeat( '-', $error->column ) . '^';
                echo "</pre>";
                echo "<p>Message: <em>" . htmlentities($error->message) . "</em></p>";
                
            }
        */  
