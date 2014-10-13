<?php
/**
 * Registration Control for User Module
 *
 * PHP Version 5.3 (min)
 *
 * @package    DigiTisk
 * @subpackage User
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace App\User\control;

class RegistrationControl extends \Nette\Application\UI\Control
{
	/** @var \App\Model\User\UserService */
	private $userService;

	/** @var array */
	private $config;

	/** @var  string */
	public $token = NULL;

	public function __construct(\App\Model\User\UserService $userService, $config)
	{
		$this->userService = $userService;
		$this->config = $config;
	}

	public function render()
	{
		if(!is_null($this->token))
		{
			$templateName = __DIR__ . '/Activation.latte';

			$noExist = FALSE;
			$alreadyActive = FALSE;
			$activationError = FALSE;

			$user = $this->userService->user_findByToken($this->token);
			if($user)
			{
				if(is_null($user['active']))
				{
					if(!$this->userService->user_activateAccount($user->id))
						$activationError = TRUE;
				}
				else
					$alreadyActive = TRUE;
			}
			else
				$noExist = TRUE;

			$this->template->noExist = $noExist;
			$this->template->alreadyActive = $alreadyActive;
			$this->template->activationError = $activationError;
			$this->template->user = $user;
		}
		else
			$templateName = __DIR__ . '/Registration.latte';

		$this->template->setFile($templateName);
		$this->template->render();
	}

	protected function createComponentRegistrationForm()
	{
		$form = new \classes\Forms\Registration();
		$form->addTo(new \classes\Forms\Password());
		$form->addText('company_name', 'F NAME');

		$form->addText('email', 'E-mail')
			->setRequired('Zadejte e-mail')
			->addRule(\Nette\Application\UI\Form::EMAIL, 'E-mail není ve správném tvaru')
			->addRule(array($this, 'verify_emailFree'), 'Zadaný e-mail je již použit');

		$form->onSuccess[] = array($this, 'registrationForm_submitted');
		return $form;
	}

	public function registrationForm_submitted(\Nette\Application\UI\Form $form)
	{
		$data = array();
		$values = $form->getValues();

		$data['name'] = $values->name;
		$data['b_name'] = \Nette\Utils\Strings::lower($values->company_name);
		$data['email'] = \Nette\Utils\Strings::lower($values->email);
		$data['b_street'] = \zvitek\Helper::pick($values->street);
		$data['b_city'] = \zvitek\Helper::pick($values->city);
		$data['b_zip'] = \zvitek\Helper::pick($values->zip);
		$data['b_ico'] = \zvitek\Helper::pick($values->ico);
		$data['b_dic'] = \zvitek\Helper::pick($values->dic);
		$data['password'] = \Nette\Security\Passwords::hash($values->password, array('salt' => $this->config['users']['passwords']['salt']));

		$data['token'] = substr(sha1($data['email'] . $data['password']), 0, 35);
		$data['created'] = new \Nette\Utils\DateTime();

		$newUser = $this->userService->i_user($data);

		if($newUser)
		{
			$this->userService->i_userRoles($newUser, $this->config['acl']['defaultRole']['user']);
			$this['mailSend']->type = 'user';
			$this['mailSend']->data = $data;

			if($this['mailSend']->sendActivationEmail())
			{
				$this->getPresenter()->flashMessage('Registrace byla úspešná. Na Váš mail byl odeslán aktivační email. Po aktviaci se budete moci přihlásit.', 'success');
				$this->getPresenter()->redirect(':User:Page:default');
			}
			else
			{
				$this->getPresenter()->flashMessage('Registrace byla úspešná, ale nezdařilo se nám zaslat Vám aktivační e-mail. Prosím, kontaktujte administrátora.', 'success');
				$this->getPresenter()->redirect('this');
			}
		}
		else
		{
			$this->getPresenter()->flashMessage('Registrace se nezdařila. Prosím skuste to znovu.', 'error');
			$this->getPresenter()->redirect('this');
		}
	}

	/**
	 * Ověří, zda se už s daným emailem někdo neregistroval
	 *
	 * @param $item
	 * @return bool
	 */
	public function verify_emailFree($item)
	{
		return !$this->userService->verify_freeEmail(\Nette\Utils\Strings::lower($item->value));
	}
}