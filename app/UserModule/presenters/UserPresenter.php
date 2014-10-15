<?php
namespace App\UserModule\Presenters;

use Nette;
use App\Model;

class UserPresenter extends BasePresenter
{
	public function renderDefault()
	{
	}

	public function renderActivateAccount($token)
	{
		$this['registration']->token = $token;
	}

	public function renderLogin()
	{
	}

	public function renderLostPassword()
	{
	}

	public function actionRevokePassword($token = NULL)
	{
		$canRevoke = $this->userService->checkExpirationLostPassword($token);
		if(!$canRevoke)
			$this->redirect('User:login');
		else
			$this['revokePassword']->userID = $canRevoke;
	}

	public function renderLogout()
	{
		$this->user->logout(TRUE);
		$this->flashMessage('Logout succesfull');
		$this->redirect(':User:Page:default');
	}

	/**
	 * Create Component Registration Control
	 * @return \App\User\control\RegistrationControl
	 */
	public function createComponentRegistration()
	{
		$component = new \App\User\control\RegistrationControl($this->userService, $this->config);
		$component->addComponent(new \classes\Mail\ActivationMails\ActivationMailGenerator($this->config), 'mailSend');
		return $component;
	}

	/**
	 * Create Component Login Control
	 * @return \App\User\control\LoginControl
	 */
	public function createComponentLogin()
	{
		$component = new \App\User\control\LoginControl($this->userService, $this->user, $this->config);
		return $component;
	}

	/**
	 * Create Component Lost Password Control
	 * @return \App\User\control\LostPasswordControl
	 */
	public function createComponentLostPassword()
	{
		$component = new \App\User\control\LostPasswordControl($this->userService, $this->user, $this->config);
		$component->addComponent(new \classes\Mail\ActivationMails\LostPasswordMailGenerator($this->config), 'mailSend');
		return $component;
	}

	/**
	 * Create Component Revoke Password Control
	 * @return \App\User\control\RevokePasswordControl
	 */
	public function createComponentRevokePassword()
	{
		$component = new \App\User\control\RevokePasswordControl($this->userService, $this->user, $this->config);
		return $component;
	}
}