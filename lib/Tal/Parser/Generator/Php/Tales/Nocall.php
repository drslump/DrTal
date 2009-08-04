<?php

namespace DrSlump\Tal\Parser\Generator\Php\Tales;

use DrSlump\Tal\Parser\Generator\Base;


/*
    Nocall expressions avoid rendering the results of a path expression.

    An ordinary path expression tries to render the object that it fetches. This
    means that if the object is a function, Script, Method, or some other kind
    of executable thing, then expression will evaluate to the result of calling
    the object. This is usually what you want, but not always. For example, if
    you want to put a DTML Document into a variable so that you can refer to its
    properties, you canÕt use a normal path expression because it will render
    the Document into a string. 
*/
class Nocall extends Base\Tales
{   
    public function evaluate()
    {
        // This will not work, we might need a $ctx->nocall which returns a dummy object
        
        // We could try to implement this by returning an object indication it
        // is an "alias" to the expression.
        // Anyway, in PHP it's of little use since we could only reference
        // object methods like in:
        // tal:define="shotcut /long/path/to/method" tal:content="shortcut"

        
        $this->_value = '$ctx->path(\'' . $this->_exp . '\', true, true)';
        $this->_exp = '';
    }
}
