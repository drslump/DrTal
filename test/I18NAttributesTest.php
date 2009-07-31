<?php

require_once 'config.php';
require_once 'PHPTAL.php';
require_once 'I18NDummyTranslator.php';

class I18NAttributesTest extends PHPUnit_Framework_TestCase
{
    function testSingle()
    {
        $t = new DummyTranslator();
        $t->setTranslation('my-title', 'mon titre');
        
        $tpl = new PHPTAL('input/i18n-attributes-01.html');
        $tpl->setTranslator($t);
        $res = trim_string($tpl->execute());
        $exp = trim_file('output/i18n-attributes-01.html');
        $this->assertEquals($exp, $res);
    }

    function testTranslateDefault()
    {
        $t = new DummyTranslator();
        $t->setTranslation('my-title', 'mon titre');
        
        $tpl = new PHPTAL('input/i18n-attributes-02.html');
        $tpl->setTranslator($t);
        $res = trim_string($tpl->execute());
        $exp = trim_file('output/i18n-attributes-02.html');
        $this->assertEquals($exp, $res);
    }

    function testTranslateTalAttribute()
    {
        $t = new DummyTranslator();
        $t->setTranslation('my-title', 'mon titre');
        
        $tpl = new PHPTAL('input/i18n-attributes-03.html');
        $tpl->sometitle = 'my-title';
        $tpl->setTranslator($t);
        $res = trim_string($tpl->execute());
        $exp = trim_file('output/i18n-attributes-03.html');
        $this->assertEquals($exp, $res);
    }

    function testMultiple()
    {
        $t = new DummyTranslator();
        $t->setTranslation('my-title', 'mon titre');
        $t->setTranslation('my-dummy', 'mon machin');
        
        $tpl = new PHPTAL('input/i18n-attributes-04.html');
        $tpl->sometitle = 'my-title';
        $tpl->setTranslator($t);
        $res = trim_string($tpl->execute());
        $exp = trim_file('output/i18n-attributes-04.html');
        $this->assertEquals($exp, $res);
    }

	function testInterpolation()
	{
		$t = new DummyTranslator();
		$t->setTranslation('foo ${someObject/method} bar ${otherObject/method} buz','ok ${someObject/method} ok ${otherObject/method} ok');
		
		$tpl = new PHPTAL('input/i18n-attributes-05.html');
		$tpl->setTranslator($t);
		$tpl->someObject = array('method' => 'good');
		$tpl->otherObject = array('method' => 'great');
		$res = trim_string($tpl->execute());
		$exp = trim_file('output/i18n-attributes-05.html');
		$this->assertEquals($exp, $res);
	}
}

?>
