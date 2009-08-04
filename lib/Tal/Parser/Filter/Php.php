<?php

namespace DrSlump\Tal\Parser\Filter;

use DrSlump\Tal\Parser\Filter;

class Php extends Filter
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