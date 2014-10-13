<?php
namespace zvitek;

use \Nette\Templating\FileTemplate;
use \Nette\Latte\Engine;
use zvitek;

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

