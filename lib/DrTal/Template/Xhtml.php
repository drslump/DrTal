<?php #$Id$
/*
 File: DrTal/Template/Xhtml.php

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

require_once DRTAL_INCLUDE_BASE . 'DrTal/Template/Xml.php';


/*
 Class: DrTal::Template::Xhtml
    Default template handler suited to parse valid XHTML templates.
    
    Assumes that the template is XML valid except for escaping javasript nodes,
    defining the xhtml namespace and declaring the HTML entities.

 Extends:
    <DrTal::Template::Xml> « <DrTal::Template> 
*/
class Xhtml extends Xml
{
    // HTML 4.01 entities as referenced in http://www.w3schools.com/tags/ref_entities.asp
    protected $entities = array(
        // ISO 8859-1
        'nbsp'=>160, 'iexcl'=>161, 'cent'=>162, 'pound'=>163, 'curren'=>164, 'yen'=>165,
        'brvbar'=>166, 'sect'=>167, 'uml'=>168, 'copy'=>169, 'ordf'=>170, 'laquo'=>171,
        'not'=>172, 'shy'=>173, 'reg'=>174, 'macr'=>175, 'deg'=>176, 'plusmn'=>177,
        'acute'=>180, 'micro'=>181, 'para'=>182, 'middot'=>183, 'cedil'=>184, 'ordm'=>186,
        'raquo'=>187, 'iquest'=>191, 'times'=>215, 'divide'=>247,
        // ISO 8859-1
        'szlig'=>223, 'agrave'=>224, 'aacute'=>225, 'acirc'=>226, 'atilde'=>227, 'auml'=>228,
        'aring'=>229, 'aelig'=>230, 'ccedil'=>231, 'egrave'=>232, 'eacute'=>233, 'ecirc'=>234,
        'euml'=>235, 'igrave'=>236, 'iacute'=>237, 'icirc'=>238, 'iuml'=>239, 'eth'=>240,
        'ntilde'=>241, 'ograve'=>242, 'oacute'=>243, 'ocirc'=>244, 'otilde'=>245, 'ouml'=>246,
        'oslash'=>248, 'ugrave'=>249, 'uacute'=>250, 'ucirc'=>251, 'uuml'=>252, 'yacute'=>253,
        'thorn'=>254, 'yuml'=>255, 
        // Maths
        'forall'=>8704, 'part'=>8706, 'exists'=>8707, 'empty'=>8709, 'nabla'=>8711,
        'isin'=>8712, 'notin'=>8713, 'ni'=>8715, 'prod'=>8719, 'sum'=>8721, 'minus'=>8722,
        'lowast'=>8727, 'radic'=>8730, 'prop'=>8733, 'infin'=>8734, 'ang'=>8736, 'and'=>8743,
        'or'=>8744, 'cap'=>8745, 'cup'=>8746, 'int'=>8747, 'sim'=>8764, 'cong'=>8773,
        'asymp'=>8776, 'ne'=>8800, 'equiv'=>8801, 'le'=>8804, 'ge'=>8805, 'sub'=>8834,
        'sup'=>8835, 'nsub'=>8836, 'sube'=>8838, 'supe'=>8839, 'oplus'=>8853, 'otimes'=>8855,
        'perp'=>8869, 'sdot'=>8901,
        // Greek
        'alpha'=>945, 'beta'=>946, 'gamma'=>947, 'delta'=>948, 'epsilon'=>949, 'zeta'=>950,
        'eta'=>951, 'theta'=>952, 'iota'=>953, 'kappa'=>954, 'lambda'=>923, 'mu'=>956,
        'nu'=>925, 'xi'=>958, 'omicron'=>959, 'pi'=>960, 'rho'=>961, 'sigmaf'=>962,
        'sigma'=>963, 'tau'=>964, 'upsilon'=>965, 'phi'=>966, 'chi'=>967, 'psi'=>968,
        'omega'=>969, 'thetasym'=>977, 'upsih'=>978, 'piv'=>982, 
        // Other
        'oelig'=>339, 'scaron'=>353, 'fnof'=>402, 'circ'=>710, 'tilde'=>732, 'ensp'=>8194,
        'emsp'=>8195, 'thinsp'=>8201, 'zwnj'=>8204, 'zwj'=>8205, 'lrm'=>8206, 'rlm'=>8207,
        'ndash'=>8211, 'mdash'=>8212, 'lsquo'=>8216, 'rsquo'=>8217, 'sbquo'=>8218,
        'ldquo'=>8220, 'rdquo'=>8221, 'bdquo'=>8222, 'dagger'=>8224, 'bull'=>8226,
        'hellip'=>8230, 'permil'=>8240, 'prime'=>8242, 'lsaquo'=>8249, 'rsaquo'=>8250,
        'oline'=>8254, 'euro'=>8364, 'trade'=>8482, 'larr'=>8592, 'uarr'=>8593, 'rarr'=>8594,
        'darr'=>8595, 'harr'=>8596, 'crarr'=>8629, 'lceil'=>8968, 'rceil'=>8969,
        'lfloor'=>8970, 'rfloor'=>8971, 'loz'=>9674, 'spades'=>9824, 'clubs'=>9827,
        'hearts'=>9829, 'diams'=>9830, 
    );
        
        
    public function getSource()
    {
        // Loads the template
        $tpl = parent::getSource();
        
        // Register HTML entities        
        foreach ( $this->entities as $name => $value ) {
            $this->parser->registerEntity( $name, "&#$value;" );
        }
        
        // Makes sure script tags are enclosed with a CDATA or a comment
        $endPos = 0;
        while ( ( $startPos = stripos($tpl, '<script', $endPos) ) !== false ) {
            $startPos = strpos( $tpl, '>', $startPos ) + 1;
            $endPos = stripos( $tpl, '</script>', $startPos );
            $commentPos = strpos( $tpl, '<!', $startPos );
            if ( $startPos !== $endPos && ($commentPos === false || $commentPos > $endPos) ) {
                // Wrap the script element contents with a CDATA
                $tpl = substr( $tpl, 0, $startPos ) .
                        "//<![CDATA[\n" .
                        substr( $tpl, $startPos, $endPos-$startPos ) .
                        '//]]>' .
                        substr( $tpl, $endPos );
            }
        }
        
        return $tpl;
    }
}