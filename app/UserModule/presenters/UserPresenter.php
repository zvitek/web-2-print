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

	public function renderProfile()
	{
		if(!$this->user->isLoggedIn())
			$this->redirect(':User:User:Login');
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
		$this->flashMessage('Logout successful');
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

	/**
	 * Create Component Profile Control
	 * @return \App\User\control\ProfileControl
	 */
	public function createComponentProfile()
	{
		$component = new \App\User\control\ProfileControl($this->user, $this->config);
		$component->addComponent(new \App\User\control\PersonalEditFormControl($this->userService, $this->user, $this->config), 'personalForm');
		$component->addComponent(new \App\User\control\PasswordEditFormControl($this->userService, $this->user, $this->config), 'passwordForm');
		$component->addComponent(new \App\User\control\TransportEditFormControl($this->userService, $this->user, $this->config), 'transportForm');
		$component->addComponent(new \App\User\control\BillingEditFormControl($this->userService, $this->user, $this->config), 'billingForm');
		$component->addComponent(new \App\User\control\MerchantEditFormControl($this->userService, $this->merchantService, $this->user, $this->config), 'merchantForm');
		return $component;
	}
}