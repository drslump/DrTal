<?php #$Id$
/*
 File: DrTal/Template/Xml.php

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

require_once DRTAL_INCLUDE_BASE . 'DrTal/Template.php';


/*
 Class: DrTal::Template::Xml
    A simple template handler for valid XML templates

 Extends:
    <DrTal::Template>
*/
class Xml extends DrTal::Template {
    
    /*
     Method: getSource
        Besides obtaining the template source this will replace any PHP short tag
        with its normal tag equivalent. This is needed to make the compiled template
        work correctly on a host with short tags on.
     
     Returns:
        The template source
    */   
    public function getSource()
    {
        $tpl = parent::getSource();
        
        // Convert php short tags to xml processing instructions
        $tpl = str_replace(
            array( '<?=', '<? ', "<?\t", "<?\r\n", "<?\n", "<?\r" ),
            array( '<?php echo ', '<?php ', "<?php\t", "<?php\r\n", "<?php\n", "<?php\r" ),
            $tpl
        );
        
        return $tpl;
    }
}
