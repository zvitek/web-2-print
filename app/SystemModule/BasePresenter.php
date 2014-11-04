<?php

namespace App\SystemModule;

use Model\Api\ApiBrewery;
use Model\Api\ApiNews;
use Model\Api\ApiProduct;
use Nette\Templating\Template;

abstract class BasePresenter extends \App\BasePresenter
{

	public function startup()
	{
		parent::startup();
	}

	/**
	 * @inject
	 * @var \Model\Acl\RolesModel
	 */
	public $aclRolesModel;



	/**
	 * Create templates methods
	 * @param string $class
	 * @return Template
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->adminLayout = __DIR__ . '/AclModule/templates/@layout.latte';
		$template->aclSideBarMenu = __DIR__ . '/AclModule/templates/Menu/aclSideBarMenu.latte';
		return $template;
	}
}

