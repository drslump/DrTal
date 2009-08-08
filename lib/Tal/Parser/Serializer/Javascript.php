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
 Class: Tal::Parser::Writer::Javascript
    This class helps in generating templates compiled in the Php language.

*/
class Javascript extends Parser\Writer
{
	protected $_output = ''; 
	protected $_indent = 0;
	protected $_tab = '    '; 
	
	protected $_xmlData = '';
	protected $_xmlIndent = 0;
	
	
	public function build()
	{
		$this->_output = '';
		$this->_indent = 0;
		
		$this->_build($this->_node);
		return $this->_output;
	}
	
	
	protected function _parsePHP($php)
	{
		$tokens = token_get_all('<?php ' . $php);
		
		$out = '';
		
		echo '<pre>';
		
		foreach($tokens as $tkn) {
			if (is_array($tkn)) {
				$type = $tkn[0];
				$value = $tkn[1];
			} else {
				$type = 0;
				$value = $tkn;
			}
			
			echo "$type " . token_name($type) . ": \"" . htmlspecialchars($value) . "\"\n";
			
			switch ($type) {
				case T_OPEN_TAG:
					// skip
				break;
				case T_VARIABLE:
					if ($value === '$ctx') {
						$out .= 'ctx';
					} else {
						// Assigning to 'this' we don't need to worry about
						// initializing the variable
						$out .= 'this.' . substr($value, 1);
					}
				break;
				case T_OBJECT_OPERATOR:
					$out .= '.';
				break;
				case T_STRING:
					if (in_array(strtoupper($value), array('NULL', 'TRUE', 'FALSE'))) {
						$value = strtolower($value);
					}
					$out .= $value;
				break;
				default:
					if ($value === '.') {
						$out .= '+';
					} else {
						// anything else is added verbatim
						$out .= $value;
					}
				break;
			}
		}
		
		//echo '</pre>';
		
		return $out;
	}
	
	protected function _echo($str)
	{
		
		if (!empty($this->_xmlData)) {
			$indent = str_repeat($this->_tab, $this->_xmlIndent);
			$this->_output .= $indent . "ctx.write('$this->_xmlData');\n";
			$this->_xmlData = '';
		}
		
		$indent = str_repeat($this->_tab, $this->_indent);
		$str = str_replace("\n", "\n$indent", $str);
		$this->_output .= $indent . $str . "\n";
	}

	protected function _xml($str, $escape = false)
	{
		$this->_xmlIndent = $this->_indent;
		
		$str = str_replace("\n", '\n', $str);
		$str = str_replace("'", "\\'", $str);
		if ($escape) {
			$str = htmlspecialchars($str);
		}
		
		$this->_xmlData .= $str;
	}
	
	protected function _buildArgs($args)
	{
		if (!is_array($args)) return '';
		
		// First generate a PHP list of arguments
		$result = array();
		foreach ($args as $arg) {
			if (is_bool($arg)) {
				$result[] = $arg ? 'true' : 'false';
			} else {
				$result[] = $arg;
			}
		}
		
		$args = implode(', ', $result);
		
		// Now convert the PHP sentence to Javascript
		return $this->_parsePHP($args);
	}	
	
	protected function _build($node)
	{
		$stack = array();
		
		foreach ($node->children as $n) {
			switch($n->mode) {
				case 'XML':
					$this->_xml($n->content);
				break;
				case 'IF':
					$this->_echo('if ( ' . $this->_parsePHP($n->condition) . ' ) {');
					$stack[] = '} /* if */';
					$this->_indent++;
				break;
				case 'ELSE':
					$this->_indent--;
					if (!empty($n->condition)) {
						$this->_echo('} else if (' . $this->_parsePHP($n->condition) . ') {');
					} else {
						$this->_echo('} else {');
					}
					$this->_indent++;
				break;
				case 'CAPTURE':
					$this->_echo('ctx.startCapture();');
					$stack[] = 'this.' . substr($n->variable, 1) . ' = ctx.endCapture();';
					$this->_indent++;
				break;
				case 'ECHO':
					$this->_xml($n->content, true);
				break;
				case 'PATH':
					//!TODO: escape?
					$this->_echo('ctx.print(ctx.path("' . $n->content . '"));');
				break;
				case 'TRY':
					$this->_echo('try {');
					$stack[] = '} /* try */';
					$this->_indent++;
				break;
				case 'CATCH':
					$this->_indent--;
					$this->_echo('} catch (' . $n->var . ') { /* ' . $n->exception . ' */');
					$this->_indent++;
				break;
				case 'THROW':
					$args = $this->_buildArgs(', ', $n->args);
					//$exception = str_replace('\\', '.', $n->exception);
					$this->_echo('throw new Error(' . $args . ');');
				break;
				case 'CODE':
					$this->_echo( $this->_parsePHP($n->content) );
				break;
				case 'COMMENT':
					$this->_echo('/*');
					$this->_indent++;
					$this->_echo($n->content);
					$this->_indent--;
					$this->_echo('*/');
				break;
				case 'ITERATE':
					$iterator = 'this.' . substr($n->iterator, 1);
					$item = 'this.' . substr($n->item, 1);
					$key = 'this.$key$';
					
					$this->_echo("for ($key in $iterator) if ($iterator.hasOwnProperty($key)) {");
					$this->_echo("{$this->_tab}$item = {$iterator}[$key];");
					
					$this->_indent++;
					
					$stack[] = '} /* for */';
				break;
				case 'TEMPLATE':
					$this->_echo('function ' . $n->ident . '(ctx){');
					$stack[] = "{$this->_tab}return ctx._result();\n} /* End of template function */";
					$this->_indent++;
				break;
				case 'CONTEXT':
					$args = $this->_buildArgs($n->args);
					$this->_echo('ctx.' . $n->method . '(' . $args . ');');
				break;
			
			}
			
			if (count($n->children)) {
				$this->_build($n);
			}
			
			while (count($stack)) {
				$this->_indent--;
				$this->_echo(array_pop($stack));
			}			
		}
	}
	

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
