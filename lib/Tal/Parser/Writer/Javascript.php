<?php #$Id$
/*
 File: Tal/Parser/Writer/Php.php

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

namespace DrSlump\Tal\Parser\Writer;

use DrSlump\Tal\Parser;

require_once TAL_LIB_DIR . 'Tal/Parser/Writer.php';
require_once TAL_LIB_DIR . 'Tal/Parser/Writer/Exception.php';


/*
 Class: Tal::Parser::Writer::Javascript
    This class helps in generating templates compiled in the Php language.

*/
class Javascript extends Parser\Writer
{ 
	public function output($node = null)
	{
		if ( NULL === $node ) $node = $this->_node;
		
		$stack = array();
		
		foreach ($node->getChildren() as $n) {
			switch($n->getMode()) {
				case 'XML':
					echo 'document.write("' . $n->content . '");';
				break;
				case 'IF':
					echo 'if (' . $n->condition . ') {';
					$stack[] = '} /* /if */';
				break;
				case 'ELSE':
					if (!empty($n->condition)) {
						echo '} else if (' . $n->condition . '){';
					} else {
						echo '} else {';
					}
				break;
				case 'CAPTURE':
					echo 'var originalDocumentWrite = document.write;';
					echo 'var ' . $n->variable . ' = "";';
					echo 'document.write = function(str){ ' . $n->variable . ' += str; }';
					$stack[] = 'document.write = originalDocumentWrite;';
				break;
				case 'ECHO':
					echo 'document.write("' . $n->content . '");';
				break;
				case 'PATH':
					echo 'document.write(ctx.path("' . $n->content . '"));';
				break;
				case 'CATCH':
					echo 'try {';
					$stack[] = '} catch (e) { alert(e); }';
				break;
				case 'THROW':
					echo 'throw new ' . $n->exepction . '("' . $n->content . '"); ?>';
				break;
			}
			
			echo "\n";
			
			
			if ($n->hasChildren()) {
				$this->output($n);
			}
		}
		
		while (count($stack)) {
			echo array_pop($stack);
			echo "\n";
		}
		
		/*
		echo '<pre>'; print_r($this->_node);
			
		echo '<hr/>';
		$this->dump($this->_node);
		*/
	}
	
	public function dump($node)
	{
		//var_dump($node);
		foreach($node->getChildren() as $n) {
			var_dump($n);
			$this->dump($n);
		}
	}
}
