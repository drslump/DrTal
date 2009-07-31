<?php

namespace DrTal::Parser::Filter;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Parser/Filter.php';

class Php extends DrTal::Parser::Filter
{

    public function text( $data )
    {        
        $data = preg_replace( '/\$\{([^\}]+)\}/', '<?php echo $ctx->path($1);?>', $data );
        return htmlspecialchars($data, ENT_NOQUOTES, 'UTF-8');
    }
    
    public function cdata( $data )
    {
        $data = preg_replace( '/\$\{([^\}]+)\}/', '<?php echo $ctx->path($1);?>', $data );
        return $data;
    }
    
    public function comment( $data )
    {
        return trim($data);
    }
    
    public function pi( $name, $data )
    {
        return $data;
    }
}