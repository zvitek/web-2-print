<?php

/**
 * Profile Control
 * PHP Version 5.3 (min)
 * @package    DigiTisk
 * @subpackage All
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace App\User\control;

class PersonalEditFormControl extends \Nette\Application\UI\Control
{
	/** @var \App\Model\User\UserService */
	private $userService;

	/** @var \Nette\Security\User */
	private $user;

	/** @var array */
	private $config;

	/** @var int */
	private $roles;

	public function __construct(\App\Model\User\UserService $userService, \Nette\Security\User $user, $config)
	{
		$this->userService = $userService;
		$this->user = $user;
		$this->config = $config;

		$this->roles = $this->user->roles;
	}

	public function render()
	{
		$this->template->setFile(\zvitek\Helper::control_template(__DIR__,'PersonalEditForm'));
		$this->setDefaults();
		$this->template->roles = $this->roles;
		$this->template->render();
	}

	public function setDefaults()
	{
		if(in_array('user', $this->roles))
		{
			$userData = $this->userService->data_user(array($this->user->id), array(), TRUE);
			if($userData)
				$this['personalForm']->setDefaults($userData);
		}
	}

	public function createComponentPersonalForm()
	{
		$form = new \classes\Forms\BaseForm();

		$form->addText('name', 'Jméno a Příjmení')
			->setRequired('Vyplňte jméno a příjmení');

		$form->addText('phone', 'Telefon');

		//RULES
		$form->phone_rules($form['phone']);
		//DAT SEM BILLING A TRANSPORT ? ALEBO ICH ROZDeLIT NA SAMOSTATNE FORMY ?

		$form->addSubmit('modify', 'Upravit');
		$form->onSuccess[] = array($this, 'personalFormSubmitted');
		return $form;
	}

	public function personalFormSubmitted(\Nette\Application\UI\Form $form)
	{
		$values = $form->getValues();

		$data = array(
			'name' => \zvitek\Helper::pick($values['name']),
			'phone' => \zvitek\Helper::pick($values['phone']),
		);

		$data = \zvitek\Helper::clearEmptyArray($data);

		if(!in_array('user', $this->roles))
		{
			$this->getPresenter()->flashMessage('Na upravování těhle informací nemáte dostatečné oprávnění', 'error');
			$this->getPresenter('this');
		}
		else
		{
			if($this->userService->i_user($data, $this->user->id))
				$this->getPresenter()->flashMessage('Položky byly upraveny', 'success');
			else
				$this->getPresenter()->flashMessage('Položky se nezdařilo upravit', 'error');

			if($this->getPresenter()->isAjax())
				$this->invalidateControl('personalForm');
			else
				$this->redirect('this');
		}
	}
}