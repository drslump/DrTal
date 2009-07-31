#!/usr/bin/php
<?php
if ($argc < 3 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

Docs Generator

  Usage:
  <?php echo $argv[0]; ?> --input=path --output=path
	
<?php
	exit();
}


function arguments($argv) {
    $_ARG = array();
    foreach ($argv as $arg) {
      	if (ereg('--([^=]+)=(.*)',$arg,$reg)) {
        	$_ARG[$reg[1]] = $reg[2];
      	} elseif(ereg('-([a-zA-Z0-9])',$arg,$reg)) {
            $_ARG[$reg[1]] = 'true';
		} 
    }
  return $_ARG;
}

$args = arguments($argv);

$srcPath = $args['input'];
$dstPath = $args['output'];
$tplPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'template';
$cfgFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'generator.ini';



$meta = parse_ini_file( $cfgFile, true );


function innerHTML( $node )
{
    $out = '';
    foreach ($node->childNodes as $child) {
        $out .= $child->ownerDocument->saveXML( $child );
    }
    return $out;
}


function createDirectory( $dir )
{
    if ( !is_dir($dir) ) {
        $rst = @mkdir($dir);
        if (!$rst)
            echo "Error creating folder $dir";
    }
}

function copyFiles( $origPath, $destPath ) 
{
    foreach( glob($origPath . DIRECTORY_SEPARATOR . '*.*') as $fname ) {
        $rst = @copy( $fname, $destPath . DIRECTORY_SEPARATOR . basename($fname) );
        if (!$rst)
            echo "Error copying $fname\n";
    }
}


function parseFile( $file, $nestingLevel = 0, $showMainTopic = false )
{
    global $meta, $tplPath;

    $data = array();

    $html = file_get_contents( $file );
    
    // The empty anchors are not parsed properly so we have to preprocess them
    $html = preg_replace('/<a\s([^>]+)><\/a>/', "<a $1>&#160;</a>", $html );
    $dom = @DOMDocument::LoadHTML( $html );
            
    $xpath = new DOMXPath( $dom );
    
    $data['main'] = $xpath->query( '//td[@class="ContentSection"]', $dom )->item(0);
    if ( !$data['main'] ) {
            $data['main'] = $xpath->query('//td[@class="IndexSection"]', $dom )->item(0);
    }
    $data['menu'] = $xpath->query( '//td[@class="MenuSection"]', $dom )->item(0);	
        
    
    if ($main = $dom->getElementById('MainTopic')) {
        if (!$showMainTopic) {
            $main->parentNode->removeChild( $main );            
        } else {
            foreach ( $xpath->query( '//div[@class="Summary"]', $main ) as $elm ) {
                $elm->parentNode->removeChild( $elm );
            }
        }
    }
    
        
    // fix empty divs in summary table by adding a &nbsp;
    foreach ( $xpath->query( '//table[@class="STable"]//td//div' ) as $div )
    {
        if ( !$div->hasChildNodes() ) {
            $div->appendChild( $dom->createEntityReference('nbsp') );
        }
    }
	
    // apply syntax highlighting
    foreach ( $xpath->query('//pre[@class="CCode"]') as $pre ) {
	
	require_once 'geshi/geshi.php';
        
        $hl = '';
        foreach ( $pre->childNodes as $child )
        {
            if ($child->nodeType == 3)
                $hl.= $child->nodeValue;
            else if (
                    $child->nodeType === 1 &&
                    strtolower($child->nodeName) === 'br' &&
                    substr($hl, -1) !== "\n" )
                $hl.= "\n";
        }
    
        $lang = 'javascript';    
        if (preg_match( '/^\s*@([A-Za-z]+)/', $hl, $matches ))
        {
            $hl = preg_replace('/^\s*@[A-Za-z]+/', '', $hl);
            $lang = $matches[1];
        }
            
        $geshi = new GeSHi( trim($hl), $lang );
        $hl = $geshi->parse_code();
            
            
        $hl = str_replace( '&nbsp;', '&#160;', $hl );
            
        $new = $dom->createDocumentFragment();
        $hl = $new->appendXML($hl);
        
        $pre->parentNode->replaceChild( $new, $pre );
	
    }	
        
    // remake the links
    foreach ( $xpath->query('//a[contains(@href,"://")=false]
                                [starts-with(@href,"#")=false]') as $lnk )
    {
        
        $href = trim( $lnk->getAttribute('href') );
        // remove javascript events
        if (strpos($href, 'javascript:') === 0)
        {
            $lnk->removeAttribute('href');
        }
        else
        {
            if ( !$href )
                continue;
                
            if ( preg_match('#^(\.\./)+([a-z]+)/(.*)#', $href, $m) )
            { 
                if ($m[2] === 'index') {
                    //$href = $m[1] . 'glossary/' . $m[3];
                    $href = str_repeat('../', $nestingLevel) . '_glossary/' . $m[3];
                } else {
                    $href = $m[1] . $m[3];
                }
            }
            
                    
            $lnk->setAttribute('href', $href);
        }
        
    } 	
	
    // remake tool tips
    foreach ( $xpath->query('//a[starts-with(@onmouseout, "HideTip")]') as $lnk )
    {	
        if ( preg_match("#'([^']+)'#", $lnk->getAttribute('onmouseout'), $m) )
        {
            $lnk->removeAttribute('onmouseover');
            $lnk->removeAttribute('onmouseout');
                
            if ( $m[1] && ($tt = $dom->getElementById($m[1])) )
            {
                $title = $tt->textContent;
                
                $lnk->setAttribute( 'title', $title );
                
                // remove the original tooltip node if still in the document
                if ($tt->parentNode) {
		    //Note: This seems to provoke a segfault (memory leak?)
                    //$tt->parentNode->removeChild($tt);
		}
            }
        }	
    }
    
    // Modify optional parameters (containing a question mark)
    foreach ( $xpath->query('//td[@class="CDLEntry"][contains(., "?")]') as $optional ) {
        $optional->setAttribute( 'class', 'CDLEntry Optional' );
        $optional->nodeValue = str_replace('?', '', $optional->nodeValue);
    }


    $data['title'] = '';
    foreach ( $xpath->query('//h1[@class="CTitle"]/text()') as $t )
        $data['title'] .= $t->nodeValue;
    if (!$data['title'])
    {
        foreach ( $xpath->query('//div[@class="IPageTitle"]/text()') as $t )
            $data['title'] .= $t->nodeValue;
    }
    
    if ( preg_match('#/index\b#', $file) )
        $data['baseUrl'] = '../';
    else
        $data['baseUrl'] = '';
    
    if ( preg_match('#/index/#', $file) ) {
        $nestingLevel++;
    }
    
    $data['baseUrl'] = str_repeat( '../', $nestingLevel );
    
    ob_start();
    include ( $tplPath . '/main.tpl' );
    $out = ob_get_contents();
    ob_end_clean();

    if( $meta['useTidy'] && function_exists( 'tidy_parse_string' ) ) {
    
        $tidyOpts = array(
            'char-encoding' => 'utf8',
            'output-xhtml' => true,
            'indent' => true,
            'indent-spaces' => 2,
            'newline' => 'LF',
            'wrap' => 200,		
            'quote-nbsp' => false,	
            'merge-divs' => false,	
            'drop-empty-paras' => false,
            'hide-comments' => true,	
        );
            
        $tidy = tidy_parse_string($out, $tidyOpts, 'utf8');
        $tidy->cleanRepair();
        $out = $tidy->value;
    }

    return $out;
}



// Create directories
createDirectory( $dstPath );
createDirectory( $dstPath . '/_resources' );
createDirectory( $dstPath . '/_resources/styles' );
createDirectory( $dstPath . '/_resources/scripts' );
createDirectory( $dstPath . '/_resources/images' );
createDirectory( $dstPath . '/_glossary' );

// Copy template resources
copyFiles( $tplPath . '/styles', $dstPath . '/_resources/styles' );
copyFiles( $tplPath . '/scripts', $dstPath . '/_resources/scripts' );
copyFiles( $tplPath . '/images', $dstPath . '/_resources/images' );




class FilenameRegexFilter extends FilterIterator
{
    protected $regex;
    
    public function __construct( Iterator $it, $regex )
    {
	parent::__construct( $it );
	$this->regex = $regex;
    }
    
    function accept()
    {
	return preg_match( $this->regex, $this->getInnerIterator()->getFilename() );
    }
}



$paths = array(
    $srcPath . '/files'	=> $dstPath,
    $srcPath . '/index' => $dstPath . '/_glossary',
);

foreach ( $paths as $srcPath => $dstPath ) {
    
    $dir = new FilenameRegexFilter(
		new RecursiveIteratorIterator(
		    new RecursiveDirectoryIterator($srcPath),
		    true
		),
		'/\.html/i'
    );
    
    foreach ( $dir as $file ) {
	
	$relpath = substr( $dir->getPath(), strlen($srcPath) );
	
	echo "Parsing " . $dir->getPathName() . PHP_EOL;
	
        //echo substr( $dir->getPathName(), strlen($srcPath) ) . PHP_EOL;
        $showMain = $meta['default'] == trim(substr( $dir->getPathName(), strlen($srcPath) ), '/ ');
	if ( preg_match('/-txt\.html$/i', $file) ) {
	    $showMain = true;
	}
	
        $html = parseFile( $file, $dir->getDepth(), $showMain );
	
	if ( !is_dir( $dstPath . DIRECTORY_SEPARATOR . $relpath ) )  {
	    mkdir( $dstPath . DIRECTORY_SEPARATOR . $relpath, 0755, true );
	}
	
	file_put_contents( $dstPath . DIRECTORY_SEPARATOR . $relpath . DIRECTORY_SEPARATOR . $dir->getFilename(), $html );
    }
    
}







