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

require_once 'lib/Tal.php';
require_once 'lib/Tal/Template/Xhtml.php';

use DrSlump\Tal;

Tal::debugging(true);

//DrTal::setClass( 'DrTal_Template_HtmlTidy' );

$tal = DrSlump\Tal::string( $xmldata );
//$tal = DrTal::load( 'test.html' );

$tal->myvar = 'MYVAR';
$tal->myvar2 = 'MYVAR2';
$tal->repeatable = array(1,2,2,2,2,3,3,3);

$out = $tal->execute();
//echo '<pre>' . htmlentities($out);
echo $out;

