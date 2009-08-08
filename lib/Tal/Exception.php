<?php

namespace DrSlump\Tal;

use DrSlump\Tal;

class Exception extends \Exception {
 
	protected $_template;
	protected $_line;
	protected $_hilite;
	
	public function setTemplate(Tal\Template $tpl)
	{
		$this->_template = $tpl;
	}
	
	public function getTemplate()
	{
		return $this->_template;
	}
	
	public function setLn($ln)
	{
		$this->_line = $ln;
	}
	
	public function getLn()
	{
		return $this->_line;
	}
	
	public function setHilite($hilite)
	{
		$this->_hilite = $hilite;
	}
	
	
	public function toHTML($lines = 5)
	{
		$src = $this->_template->getSource();
		$src = explode("\n", $src);
		
		// If a token to hilite is supplied then we can use to ensure that
		// the reported line is correct. This is specially important for
		// interpolated path expressions in the middle of text nodes, since the
		// reported line number is the one from the previous open tag only.
		if ($this->_hilite) {
			$lnNo = $this->_line;
			while ($lnNo < count($src)) {
				$ln = $src[$lnNo];
				if (preg_match('/\b' . preg_quote($this->_hilite, '/') . '\b/i', $ln)) {
					$this->_line = $lnNo;
					break;
				}
				$lnNo++;
			}
		}

		// It's common to format the tag attributes in multiple lines since
		// they can be quite long. So we need to be sure that we hilite not only
		// the offending line (where the tag starts) but also all the lines
		// used by the tag opener.
		$selected = 0;
		$ln = $src[$this->_line];
		if (strrpos($ln, '<') !== false && strrpos($ln, '<') >= strrpos($ln, '>')) {
			do {
				$selected++;
				$ln = $src[$this->_line + $selected];
			} while (strpos($ln, '>') === false);
		}

		// Calculate from which line number to which we need to show
        $min = max($this->_line - floor($lines/2), 0);
        $max = min($min+$selected+$lines, count($src));
		
		
		$title = htmlspecialchars($this->getMessage());
		$tplName = htmlspecialchars($this->_template->getName());
		$lnNo = $this->_line + 1;
		
		$out = "
		<style type=\"text/css\">
		.drtal-exception {
			width: 100%;
			border: 1px solid #ccc;
			color: #111;
		}
		.drtal-exception thead td {
			border-bottom: 1px solid #ccc;
			background: white;
		}
		
		.drtal-exception tbody .hilite {
			font-weight: bold;
			padding: 0 0.1em;
			/*background-color: #ef8f8f*/
			background-image: url(/drtal/extra/underline.png);
			background-position: left bottom;
			background-repeat: repeat-x;
			color: #6f0000;

		}
		.drtal-exception tbody td {
			font: monospace;
			background: #eee;
			padding: 0 .5em;
			line-height: 1.2em;
			white-space: pre;
		}
		.drtal-exception tbody .even td {
			background: #e5e5e5;
		}
		.drtal-exception tbody .focus td {
			background: #ffdada;
		}
		.drtal-exception tbody .even.focus td {
			background: #ffd0d0;
		}
		.drtal-exception tbody td.line {
			text-align: right;
			width: 1%;
			padding: 0 .5em;
			background: #eee !important;
			border-right: 1px solid #ccc;
		}
		</style>
		<table class=\"drtal-exception\" rowspacing=\"0\" cellspacing=\"0\">
			<thead>
				<tr>
					<td colspan=\"2\">$title @ $tplName:$lnNo</td>
				</tr>
			</thead>
			<tbody>";
		
        for ($i=$min; $i<$max; $i++) {
			
            $str = htmlspecialchars($src[$i]);
			
			$rowclass = (($i+1)%2 ? 'odd' : 'even');
			if ($i >= $this->_line && $i <= $this->_line + $selected) {
				$rowclass .= ' focus';

				if ($this->_hilite) {
					$esc = preg_quote($this->_hilite, '/');
					$regexp = "/\\\$\{$esc\}|\\\$$esc|\b$esc\b/i";
					$str = preg_replace_callback($regexp, function($m) use ($title) {
						return '<span class="hilite" title="' . $title . '">' . htmlspecialchars($m[0]) . '</span>';
					}, $str);
				}
			}

			$out .= '<tr class="' . $rowclass . '">';
			$out .= '<td class="line">' . ($i+1) . '</td>';
            $out .= '<td>' . $str . '</td>';
            
            $out .= "</tr>";
        }
        $out .= '</tbody></table>';
		
		return $out;
	}
}