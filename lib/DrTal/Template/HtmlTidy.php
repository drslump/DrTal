<?php #$Id$
/*
 File: DrTal/Template/HtmlTidy.php

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

namespace DrTal::Template;

require_once DRTAL_INCLUDE_BASE . 'DrTal/Template/Xhtml.php';


/*
 Class: DrTal::Template::HtmlTidy
    Template handler for non xml compliant HTML templates.
    
    It will further process the result of <DrTal_Template_Xhtml> using PHP's
    Tidy extension to clean up the original source, fixing unclosed tags and other
    HTML nuances.

 Requires:
    PHP's Tidy extension. See http://www.php.net/tidy

 Extends:
    <DrTal::Template::Xhtml> « <DrTal::Template::Xml> « <DrTal::Template> 
*/
class HtmlTidy extends Xhtml
{
    public function getSource()
    {
        if ( !class_exists('tidy') ) {
            throw new DrTal::Exception( 'Tidy extension not available. Unable to load a template using tidy' );
        }
        
        // Register HTML entities
        $parser = DrTal::parser();
        foreach ( $this->entities as $name => $value ) {
            $parser->registerEntity( ucfirst($name), "&#$value;" );
            $parser->registerEntity( strtoupper($name), "&#$value;" );
        }        
           
        // Loads the template and applys the Xhtml changes (Entities and enclosed <script>s)
        $tpl = parent::getSource();
        
        $tidy = new Tidy();
        $config = array(
            'output-xhtml'            => true,
            'add-xml-decl'          => false,
            'add-xml-space'         => true,
            'assume-xml-procins'    => true,
            'doctype'               => 'omit',
            'drop-empty-paras'      => false,
            'drop-propietary-attributes'    => false,
            'escape-cdata'          => false,
            'fix-backspace'         => false,
            'fix-uri'               => false,            
            'join-classes'          => true,
            'join-styles'           => true,
            'literal-attributes'    => true,
            //'lower-literals'        => true,
            'merge-divs'            => false,
            'merge-spans'           => false,
            'preserve-entities'     => true,
            'quote-ampersand'       => true,
            'quote-nbsp'            => true,
            'repeated-attributes'   => 'keep-last',
        );
        
        $tidy->parseString( $tpl, $config );
        
        return (string)$tidy;
    }
    
}
