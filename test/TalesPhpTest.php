<?php

require_once 'config.php';
require_once 'PHPTAL.php';

class TalesPhpTest extends PHPUnit_Framework_TestCase {
	
	function testMix(){
		$tpl = new PHPTAL('input/php.html');
		$tpl->real = 'real value';
		$tpl->foo = 'real';
		$res = trim_string($tpl->execute());
		$exp = trim_file('output/php.html');
		$this->assertEquals($exp,$res);
	}
}

?>
