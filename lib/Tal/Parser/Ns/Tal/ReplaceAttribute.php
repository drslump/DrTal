<?php

namespace DrSlump\Tal\Parser\Ns\Tal;


class ReplaceAttribute extends ContentAttribute
{
    public function beforeElement()
    {
        // Call tal:content's beforeContent() handler
        parent::beforeContent();
    }
    
    public function beforeContent()
    {
        // just defined to skip the original ContentAttribute behaviour
    }
    
    public function afterContent()
    {
        // just defined to skip the original ContentAttribute behaviour
    }

    public function afterElement()
    {
        // Call tal:content's afterContent() handler
        parent::afterContent();
    }
}