<?php
/**
 * Login Control for User Module
 * PHP Version 5.3 (min)
 * @package    DigiTisk
 * @subpackage User
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace App\User\control;

class RevokePasswordControl extends \Nette\Application\UI\Control
{
	/** @var \App\Model\User\UserService */
	private $userService;

	/** @var array */
	private $config;

	/** @var \Nette\Security\User */
	private $user;

	/** @var  int */
	public $userID;

	public function __construct(\App\Model\User\UserService $userService, \Nette\Security\User $user, $config)
	{
		$this->userService = $userService;
		$this->user = $user;
		$this->config = $config;
	}

	public function render()
	{
		$this->user->logout(TRUE);
		$this->template->setFile(__DIR__ . '/templates/RevokePassword.latte');
		$this->template->render();
	}

	protected function createComponentRevokePasswordForm()
	{
		$form = new \classes\Forms\Password();

		$form->addSubmit('revoke', 'Obnovit Heslo');
		$form->onSuccess[] = array($this, 'revokePasswordForm_submitted');
		return $form;
	}

	public function revokePasswordForm_submitted(\Nette\Application\UI\Form $form)
	{
		$values = $form->getValues();
		if(\Nette\Utils\Validators::isNumericInt($this->userID))
		{
			$dataToSend = $this->userService->data_user(array($this->userID), array(), TRUE);
			if(!count($dataToSend))
			{
				$this->getPresenter()->flashMessage('Aktualizace vašeho hesla selhala. Prosím, kontaktujte administrátora.', 'error');
				$this->getPresenter()->redirect('this');
			}
			else
			{
				$newPassword = \Nette\Security\Passwords::hash($values['password']);
				$dataToSend['password'] = $values['password'];

				$updatePassword = $this->userService->i_user(array('password' => $newPassword), $this->userID);
				$expireTokensForLostPassword = $this->userService->user_expireAllLostPasswords($this->userID);

				if($updatePassword && $expireTokensForLostPassword)
				{
					$this->getPresenter()->flashMessage('Nové heslo bylo uloženo, můžete se přihlásit.', 'success');
					$this->getPresenter()->redirect('this');
				}
				else
				{
					$this->getPresenter()->flashMessage('Aktualizace vašeho hesla selhala. Prosím, kontaktujte administrátora.', 'success');
					$this->getPresenter()->redirect('this');
				}
			}
		}
		else
		{
			$this->getPresenter()->flashMessage('S touhle e-mailovou adresou není provázán žádný účet. Jestli jste si jisti, že jste použili tenhle email pri registraci, prosím, kontaktujte administrátora.', 'success');
			$this->getPresenter()->redirect('this');
		}
	}
}