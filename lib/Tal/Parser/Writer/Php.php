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


/*
 Class: Tal::Parser::Writer::Php
    This class helps in generating templates compiled in the Php language.

*/
class Php extends Parser\Writer
{
	protected $_indent = 0;
	protected $_output = '';
	
	protected function _echo($str, $isPHP = true)
	{
		static $php;
		
		$indent = str_repeat("    ", $this->_indent);
		
		if ($php != $isPHP) {
			$php = $isPHP;
			$this->_output .= $isPHP ? "<?php" : "\n$indent?>";
		}
		
		if ($isPHP) {
			$this->_output .= "\n$indent";
		}
		
		$this->_output .= $str;
	}
	
	public function build()
	{
		$this->_output = '';
		$this->_indent = 0;
		
		$this->_build($this->_node);
		return $this->_output;
	}
	
	protected function _buildArgs($args)
	{
		if (!is_array($args)) return '';
		
		$result = array();
		foreach ($args as $arg) {
			if (is_bool($arg)) {
				$result[] = $arg ? 'true' : 'false';
			} else {
				$result[] = $arg;
			}
		}
		
		return implode(', ', $result);
	}
	
	protected function _build($node)
	{
		$stack = array();
		
		foreach ($node->getChildren() as $n) {
			
			switch($n->getMode()) {
				case 'XML':
					$this->_echo($n->content, false);
				break;
				case 'IF':
					$this->_echo('if (' . $n->condition . '):');
					$stack[] = 'endif;';
					$this->_indent++;
				break;
				case 'ELSE':
					$this->_indent--;
					if (!empty($n->condition)) {
						$this->_echo('elseif (' . $n->condition . '):');
					} else {
						$this->_echo('else:');
					}
					$this->_indent++;
				break;
				case 'CAPTURE':
					$this->_echo('ob_start();');
					$stack[] = $n->variable . ' = ob_get_clean();';
					$this->_indent++;
				break;
				case 'ECHO':
					$this->_echo('echo htmlspecialchars("' . $n->content . '");');
				break;
				case 'VAR':
					$this->_echo('echo $' . $n->content . ';');
				break;
				case 'PATH':
					$this->_echo('echo $ctx->path("' . $n->content . '");');
				break;
				case 'TRY':
					$this->_echo('try {');
					$stack[] = '} /* try */';
					$this->_indent++;
				break;
				case 'CATCH':
					$this->_indent--;
					$this->_echo('} catch (' . $n->exception . ' ' . $n->var . ') {');
					$this->_indent++;
				break;
				case 'THROW':
					$args = $this->_buildArgs(', ', $n->args);
					$this->_echo('throw new ' . $n->exception . '(' . $args . ');');
				break;
				case 'CODE':
					$this->_echo($n->content);
				break;
				case 'COMMENT':
					$this->_echo('/* ' . $n->content . ' */');
				break;
				case 'ITERATE':
					$this->_echo('foreach(' . $n->iterator . '):');
					$stack[] = 'endforeach;';
					$this->_indent++;
				break;
				case 'TEMPLATE':
					$this->_echo('use DrSlump\\Tal;');
					$this->_echo('function ' . $n->ident . '($ctx){');
					$stack[] = '} /* End of template function */';
					$this->_indent++;
				break;
				case 'CONTEXT':
					$args = $this->_buildArgs($n->args);
					$this->_echo('$ctx->' . $n->method . '(' . $args . ');');
				break;
			}
			
			if ($n->hasChildren()) {
				$this->_build($n);
			}
			
			while (count($stack)) {
				$this->_indent--;
				$this->_echo(array_pop($stack));
			}
			
		}
		
		
		//echo '<pre>'; print_r($this->_node);
		/*	
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
