<?php
/**
 * Login Control for User Module
 *
 * PHP Version 5.3 (min)
 *
 * @package    DigiTisk
 * @subpackage User
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace App\User\control;

class LoginControl extends \Nette\Application\UI\Control
{
	/** @var \App\Model\User\UserService */
	private $userService;

	/** @var array */
	private $config;

	/** @var \Nette\Security\User */
	private $user;

	public function __construct(\App\Model\User\UserService $userService, \Nette\Security\User $user, $config)
	{
		$this->userService = $userService;
		$this->user = $user;
		$this->config = $config;
	}

	public function render()
	{
		$this->user->logout(TRUE);
		$this->template->setFile(__DIR__ . '/Login.latte');
		$this->template->render();
	}

	protected function createComponentLoginForm()
	{
		$form = new \classes\Forms\Login();

		$form->onSuccess[] = array($this, 'loginForm_submitted');
		return $form;
	}

	public function loginForm_submitted(\Nette\Application\UI\Form $form)
	{
		$values = $form->getValues();

		$this->user->login($values['username'], $values['password']);

		if($this->user->isLoggedIn())
		{
			$this->getPresenter()->redirect('Page:default');
		}
	}
}