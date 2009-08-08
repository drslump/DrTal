<?php #$Id$
/*
 File: Tal/Parser/Serializer/Php.php

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

namespace DrSlump\Tal\Parser\Serializer;

use DrSlump\Tal\Parser;


/*
 Class: Tal::Parser::Serializer::Php
    This class helps in generating templates compiled in the Php language.

*/
class Php extends Parser\Serializer
{
	protected $_indent = 0;
	protected $_output = '';
	protected $_php = false;
	protected $_append = array();
	protected $_inline = false;
	
	protected function _echo($str, $isPHP = true)
	{
		$indent = str_repeat("    ", $this->_indent);
		
		if ($this->_php != $isPHP) {
			$this->_php = $isPHP;
			$this->_output .= $isPHP ? "<?php" : "\n$indent?>";
		}
		
		if ($this->_php) {
			$this->_output .= $this->_inline ? '' : "\n$indent";
			$str = str_replace("\n", "\n$indent", $str);
		}
		
		$this->_output .= $str;
	}
	
    public function serialize(Parser\OpcodeList $opcodes)
	{
		$this->_output = '';
		$this->_indent = 0;
		
		$this->_transform($opcodes);
		
		return $this->_output . "\n" . implode("\n", $this->_append);
	}

	protected function _transform($opcodes) {
		
		$closing = new \SplStack();
		foreach ($opcodes as $op) {
			switch($op->getName()) {
				case 'XML':
					$this->_echo($op->arg(), false);
				break;
				case 'IF':
					$this->_echo('if (' . $op->arg() . '):');
					$closing->push('endif;');
					$this->_indent++;
				break;
				case 'ELSE':				
					$this->_indent--;
					if ($op->arg() === null) {
						$this->_echo('else:');
					} else {
						$this->_echo('elseif (' . $op->arg() . '):');
					}
					$this->_indent++;
				break;
				case 'CAPTURE':
					// ob_* functions are pretty fast so we can use them even
					// if their captured content is never going to be used
					$this->_echo('ob_start();');
					if ($op->arg()) {
						$closing->push($op->arg() . ' = ob_get_clean();');						
					} else {
						$closing->push('ob_end_clean();');
					}
					$this->_indent++;
				break;
				case 'ECHO':
					$this->_echo('echo htmlspecialchars("' . $op->arg() . '");');
				break;
				case 'VAR':
					$this->_echo('echo $' . $op->arg() . ';');
				break;
				case 'PATH':
					$this->_echo('echo $ctx->path("' . $op->arg() . '");');
				break;
				case 'TRY':
					$this->_echo('try {');
					$closing->push('} /* try */');
					$this->_indent++;
				break;
				case 'CATCH':
					$this->_indent--;
					$this->_echo('} catch (' . $op->arg() . ' ' . $op->arg(1) . ') {');
					$this->_indent++;
				break;
				case 'THROW':
					$args = $this->_buildArgs(', ', $op->arg(1));
					$this->_echo('throw new ' . $op->arg(0) . '(' . $args . ');');
				break;
				case 'CODE':
					$this->_echo($op->arg());
				break;
				case 'COMMENT':
					if (strpos($op->arg(), "\n") === false) {
						$this->_echo('# ' . $op->arg());
					} else {
						$this->_echo('/*');
						$this->_indent++;
						$this->_echo($op->arg());
						$this->_indent--;
						$this->_echo('*/');
					}
				break;
				case 'ITERATE':
					$this->_echo('foreach(' . $op->arg(0) . ' as ' . $op->arg(1) . '):');
					$closing->push('endforeach;');
					$this->_indent++;
				break;
				case 'TEMPLATE':
					$this->_echo('function ' . $op->arg() . '($ctx){');
					$closing->push('} /* End of template function */');
					$this->_indent++;
				break;
				case 'CONTEXT':
					$args = $this->_buildArgs($op->arg(1));
					$this->_echo('$ctx->' . $op->arg() . '(' . $args . ')' . ($this->_inline ? '' : ';') );
				break;
				case 'APPEND':
					$closing->push(array(
						'indent' => $this->_indent,
						'output' => $this->_output,
						'php' => $this->_php,
					));
					
					$this->_indent = 0;
					$this->_output = '';
					$this->_php = true;
				break;
				case 'ASSIGN':
					
					$this->_echo($op->arg(0) . ' = ');
					$this->_inline = true;
					$statement = $op->arg(1);
					if ($statement instanceof Parser\OpcodeList) {
						$this->_transform($op->arg(1));						
					} else {
						$this->_echo($this->_buildArgs(array($statement)));
					}
					$this->_echo(';');
					$this->_inline = false;
					
				break;
				case 'END':
					$statement = $closing->pop();
					if (is_array($statement)) {
						$this->_append[] = $this->_output;
						$this->_output = $statement['output'];
						$this->_indent = $statement['indent'];
						$this->_php = $statement['php'];
					} else {
						$this->_indent--;
						$this->_echo($statement);
					}
				break;
				default:
					throw new Parser\Serializer\Exception($op->getName() . ' is not supported by the serializer');
				break;
			}			
		}
		
		if ( !$closing->isEmpty() ){
			throw new Parser\Serializer\Exception('The program is malformed, closing statements missing');
		}
    }
	
	protected function _buildArgs($args)
	{
		if (!is_array($args)) return '';
		
		$result = array();
		foreach ($args as $arg) {
			if (is_string($arg) && strpos($arg, '$') === 0) {
				$result[] = $arg;
			} else {
				$result[] = var_export($arg, true);				
			}
		}
		
		return implode(', ', $result);
	}	
}
