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

class LostPasswordControl extends \Nette\Application\UI\Control
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
		$this->template->setFile(__DIR__ . '/LostPassword.latte');
		$this->template->render();
	}

	protected function createComponentLostPasswordForm()
	{
		$form = new \Nette\Application\UI\Form();

		$form->addText('email', 'Email, kterým ste se registrovali :')
			->setRequired('Zadejte e-mail')
			->addRule(\Nette\Application\UI\Form::EMAIL, 'E-mail není ve správném tvaru');

		$form->onSuccess[] = array($this, 'lostPasswordForm_submitted');
		return $form;
	}

	public function lostPasswordForm_submitted(\Nette\Application\UI\Form $form)
	{
		$values = $form->getValues();

		$user = $this->userService->user_findByEmail($values['email']);
		if($user)
		{
			$dataToSend = $this->userService->data_user(array($user['id']), array(), TRUE);
			$dataToSend['token'] = $this->userService->user_setLostPassword($user['id']);

			$this['mailSend']->type = 'user';
			$this['mailSend']->data = $dataToSend;

			if($this['mailSend']->sendLostPasswordEmail())
			{
				$this->getPresenter()->flashMessage('Žádost o nové heslo byla zpracována. Na Váš email byl odeslán link pro změnu hesla.', 'success');
				$this->getPresenter()->redirect(':User:Page:default');
			}
			else
			{
				$this->getPresenter()->flashMessage('Žádost o nové heslo byla zpracována, no nezdařilo se nám odeslat Vám link na upravení na Váš email. Prosím, kontaktujte administrátora.', 'success');
				$this->getPresenter()->redirect('this');
			}
		}
		else
		{
			$this->getPresenter()->flashMessage('S touhle e-mailovou adresou není provázán žádný účet. Jestli jste si jisti, že jste použili tenhle email pri registraci, prosím, kontaktujte administrátora.', 'success');
			$this->getPresenter()->redirect('this');
		}
	}
}