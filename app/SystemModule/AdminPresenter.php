<?php

namespace App\SystemModule;

use Model\Api\ApiBrewery;
use Model\Api\ApiNews;
use Model\Api\ApiProduct;
use Nette\Templating\Template;

abstract class AdminPresenter extends \App\BasePresenter
{

	public function startup()
	{
		parent::startup();
	}

}

