<?php

use Nette\Environment;

class Render
{
    public static function contentData($content)
    {
        $config = Environment::getContext()->parameters;

        if(array_key_exists('web', $config) && array_key_exists('utm', $config))
        {
            if(array_key_exists('replaceUni', $config['utmReplace']))
            {
                $content = strtr($content, $config['utmReplace']['replaceUni']);
            }
        }

        $content = str_replace('../../data', '', $content);
        return $content;
    }

}

