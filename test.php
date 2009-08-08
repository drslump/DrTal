<?php
ob_start();
echo '<?xml version="1.0" encoding="utf8" ?>'. PHP_EOL;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html onload="javascript: alert('this is an alert')">
<head>
    <script src="js.js"></script>
    <script type="text/javascript">
    function test() {
        var a = 1 < 2; 
    }
    </script>
    <div metal:define-macro="testmacro">
        <span metal:define-slot="news_place">
            <table>
              <tr tal:repeat="item php:latestNews()">
                <td tal:content="item/value">news description</td>
              </tr>
            </table>
        </span>
    </div>
</head>

<br/>
<br tal:content="news" />

<div metal:use-macro="testmacro">
    <span tal:condition="logged" metal:fill-slot="news_place">
      <h2>user menu</h2>
      <ul>
        <li><a href="/user/action/inbox">inbox</a></li>
        <li><a href="/user/action/new">new mail</a></li>
        <li><a href="/user/action/disconnect">disconnect</a></li>
      </ul>
    </span>    
</div>
<tal:block repeat="item items" content="item"><?='<'?>?=$ctx->item?></tal:block>
<div tal:repeat="item array" c="">  asdas  das
    &nbsp;  &hellip; &gt; &lt; &quot; &apos;
    <!-- This is a comment -->
    <strong tal:omit-tag="">Item: <span tal:content="item | nothing"/></strong>
    Index: <span tal:condition="1" tal:content="repeat/item/index"/>
    Number: <span tal:attributes="class .this | title This is my title" tal:content="repeat/item/number"/>
    Length: <span tal:content="repeat/item/length"/>
    Odd: <span tal:content="repeat/item/odd"/>
    Even: <span tal:content="repeat/item/even"/>
    Start: <span tal:content="repeat/item/start"/>
    End: <span tal:content="repeat/item/end"/>
    Roman: <span tal:replace="repeat/item/Roman"/>
    Letter: <span tal:replace="repeat/item/Letter"/>
</div>
</html>
<?php
ob_end_clean();
ob_start();
?>

<ul tal:on-error="structure string:&lt;b>Error!&lt;/b>">
    <li tal:repeat="item repeatable">
        
        <h1 tal:condition="not:repeat/item/first" tal:content="repeat/item/length"></h1>
        <p tal:content="item"></p>
        
    </li>    
</ul>

<span class="DEF"
      tal:attributes="class myvar | default"
      tal:define="mydef nothing; mydef2 myvar2"
      tal:condition="myvar"
      tal:content="mydef2">Conditioned</span>
<!-- span tal:content="my_var | my_var2 | took | default">D"e\'faul't</span -->
<br/>
<span tal:replace="structure my_var | myvar2 | took | default">D"e\'faul't</span>
<?php
$xmldata = ob_get_contents();
ob_end_clean();


ob_start();
?>
<span tal:content="missing | default" />
<ul class="bar" tal:attributes="class 'foo'|default">
    <li tal:repeat="item repeatable">
        <span class="love"
              title="This is a span"
              tal:content="myvar | foo">
            Default item
        </span>
    </li>
</ul>
<p class="classname" tal:replace="'hello $myvar world' | nothing">A paragraph</p>
<hr />
<p>Hello ${username}!</p>
<hr />
<p tal:condition="exists:myvar22">Conditioned</p>
<?php
$xmldata = ob_get_clean();

/*
require_once 'lib/Tal/Parser/Util/PriorityArrayObject.php';

$heap = new DrSlump\Tal\Parser\Util\PriorityArrayObject();
$heap->insert( 'foo', 5 );
$heap->insert( 'bar', -5 );
$heap->insert( 'bax', 9 );

foreach ($heap as $o) {
    var_dump($o);
}

foreach ($heap as $o) {
    var_dump($o);
}

exit;
*/

/*
$max = 2000000;
$prob = 10;

$start = microtime(true);

for ($i=0; $i<$max; $i++) {
    $var = $i % 10;
    if ($var == 0) $var = null;
    if ($var === NULL) {
        $error = 'E';
    }
}

echo '<pre>Time REF: ' . (microtime(true)-$start) . '</pre>';



$start = microtime(true);

for ($i=0; $i<$max; $i++) {
    $var = $i % $prob;
    if ($var == 0) $var = NULL;
    if (!$var) {
        if ($var === NULL) {
            $error = 'E';
        }
    }
}

echo '<pre>Time IF: ' . (microtime(true)-$start) . '</pre>';


$start = microtime(true);

for ($i=0; $i<$max; $i++) {
    $var = $i % $prob;
    if ($var == 0) $var = NULL;
    if (!$var):
        if ($var === NULL):
            $error = 'E';
        endif;
    endif;
}

echo '<pre>Time IF2: ' . (microtime(true)-$start) . '</pre>';


$start = microtime(true);

for ($i=0; $i<$max; $i++) {
    try {
        $var = $i % $prob;
        if (!$var) {
            throw new Exception();
        }
    } catch (Exception $e) {
        // if the are no more alternatives check if we actually have a value
        if ($var === null)
            $error = 'E';
    }
}

echo '<pre>Time TRY: ' . (microtime(true)-$start) . '</pre>';


$start = microtime(true);

$ex = new Exception();
for ($i=0; $i<$max; $i++) {
    try {
        $var = $i % $prob;
        if (!$var) {
            throw $ex;
        }
    } catch (Exception $e) {
        // if the are no more alternatives check if we actually have a value
        if ($var === null)
            $error = 'E';
    }
}

echo '<pre>Time TRY2: ' . (microtime(true)-$start) . '</pre>';
*/


require_once 'lib/Tal.php';
require_once 'lib/Tal/Template/Xhtml.php';
require_once 'lib/Tal/Template/HtmlTidy.php';

/*
require_once 'lib/Tal/Parser/OpcodeList.php';
require_once 'lib/Tal/Parser/Opcode.php';
require_once 'lib/Tal/Parser/Serializer.php';
require_once 'lib/Tal/Parser/Serializer/PHP.php';

$op = new DrSlump\Tal\Parser\OpcodeList();
$op->template('foo')
    ->context('push')
    ->echo('bar')
    ->iterate('bar')
        ->try()
            ->path()
        ->catch('foo')
        ->endTry()
        ->path()
    ->endIterate()
->endTemplate();


$it = new RecursiveIteratorIterator($op, RecursiveIteratorIterator::SELF_FIRST);
foreach ($it as $o) {
//foreach ($op as $o) {
    echo $it->getDepth() . '- ' . $o->getName() . '<br/>';
    //var_dump($o);
}

$ser = new DrSlump\Tal\Parser\Serializer\Php( new DrSlump\Tal\Template\Xhtml( new DrSlump\Tal\Storage\File(), 'test') );
highlight_string( $ser->build($op) );

exit;
*/


use DrSlump\Tal;

//try {

    Tal::debugging(true);
    
    //Tal::setTemplateClass('DrSlump\\Tal\\Template\\HtmlTidy');
    
    echo '<pre>' . htmlspecialchars($xmldata) . '</pre><hr/>';
    
    $tal = DrSlump\Tal::string( $xmldata );
    //$tal = DrSlump\Tal::load( 'test2.html' );
    
    if (is_readable($tal->getScriptPath())) {
        highlight_file($tal->getScriptPath());
        //echo '<pre>' . htmlspecialchars( file_get_contents($tal->getScriptPath()) ) .  '</pre>';
        unlink($tal->getScriptPath());
    }
    
    $tal->news = 'NEWS';
    $tal->items = array('A', 'B', 'C');
    $tal->array = array(1,2,3);
    
    $tal->username = 'USERNAME';
    $tal->myvar = 'MYVAR';
    $tal->myvar2 = 'MYVAR2';
    $tal->repeatable = array(0,2,2,2,2,3,3,3);
    
    $tal->execute();
    
//} catch (Tal\Exception $e) {
    //echo $e->toHTML(10);
//}