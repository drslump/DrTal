<?php

require_once 'config.php';
require_once 'PHPTAL.php';
require_once 'I18NDummyTranslator.php';

class I18NNameTest extends PHPUnit_Framework_TestCase
{
    function testSet()
    {
        $tpl = new PHPTAL('input/i18n-name-01.html');
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $this->assertEquals(true, array_key_exists('test', $tpl->getTranslator()->vars));
        $this->assertEquals('test value', $tpl->getTranslator()->vars['test']);
    }

    function testInterpolation()
    {
        $tpl = new PHPTAL('input/i18n-name-02.html');
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $res = trim_string($res);
        $exp = trim_file('output/i18n-name-02.html');
        $this->assertEquals($exp, $res);
    }

    function testMultipleInterpolation()
    {
        $tpl = new PHPTAL('input/i18n-name-03.html');
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $res = trim_string($res);
        $exp = trim_file('output/i18n-name-03.html');
        $this->assertEquals($exp, $res);
    }

    function testBlock()
    {
        $tpl = new PHPTAL('input/i18n-name-04.html');
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $res = trim_string($res);
        $exp = trim_file('output/i18n-name-04.html');
        $this->assertEquals($exp, $res);        
    }

    function testI18NBlock()
    {
        $tpl = new PHPTAL('input/i18n-name-05.html');
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $res = trim_string($res);
        $exp = trim_file('output/i18n-name-05.html');
        $this->assertEquals($exp, $res);        
    }

    function testNamespace()
    {
        $tpl = new PHPTAL('input/i18n-name-06.html');
        $tpl->username = 'john';
        $tpl->mails = 100;
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $res = trim_string($res);
        $exp = trim_file('output/i18n-name-06.html');
        $this->assertEquals($exp, $res);        
    }
}

