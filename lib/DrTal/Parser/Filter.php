<?php

namespace DrTal::Parser;

abstract class Filter {
    
    public function whitespace( $data )
    {
        return $data;
    }

    public function text( $data )
    {
        return $data;
    }
    
    public function cdata( $data )
    {
        return $data;
    }
    
    public function comment( $data )
    {
        return $data;
    }
    
    public function pi( $name, $data )
    {
        return $data;
    }
}