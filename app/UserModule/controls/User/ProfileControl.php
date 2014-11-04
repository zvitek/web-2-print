<?php

/**
 * Profile Control
 * PHP Version 5.3 (min)
 * @package    DigiTisk
 * @subpackage All
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace App\User\control;

class ProfileControl extends \Nette\Application\UI\Control
{
	/** @var \Nette\Security\User */
	private $user;

	/** @var array */
	private $config;

	/** @var  array */
	private $roles;

	public function __construct(\Nette\Security\User $user, $config)
	{
		$this->user = $user;
		$this->config = $config;
		$this->roles = $this->user->roles;
	}

	public function render()
	{
		$this->template->setFile(\zvitek\Helper::control_template(__DIR__,'Profile'));
		$this->template->roles = $this->roles;
		$this->template->render();
	}
}