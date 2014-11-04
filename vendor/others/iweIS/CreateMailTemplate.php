<?php
use \Nette\Templating\FileTemplate;
use \Nette\Latte\Engine;

class IweTemplate
{
    public static function createMailTemplate($path)
    {
        $template = new FileTemplate($path);
        $template->registerFilter(new Engine);
        $template->registerHelperLoader('Nette\Templating\Helpers::loader');

        return $template;
    }

}

