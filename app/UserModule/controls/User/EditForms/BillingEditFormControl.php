<?php

/**
 * Profile Control
 * PHP Version 5.3 (min)
 * @package    DigiTisk
 * @subpackage All
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace App\User\control;

class BillingEditFormControl extends \Nette\Application\UI\Control
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
		$this->template->setFile(__DIR__ . '/templates/BillingEditForm.latte');
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
				$this['billingForm']->setDefaults($userData['billing']);
		}
	}

	public function createComponentBillingForm()
	{
		$form = new \classes\Forms\BaseForm();

		$form->addText('b_name', 'Jméno a příjmení');
		$form->addText('b_street', 'Ulice');
		$form->addText('b_city', 'Město');
		$form->addText('b_zip', 'PSČ');

		$form->addText('b_ico', 'IČ');

		$form->addText('b_dic', 'DIČ');


		// RULES
		$form->zip_rules($form['b_zip']);
		$form->ico_rules($form['b_ico']);
		$form->dic_rules($form['b_dic']);

		$form->addSubmit('modify', 'Upravit');
		$form->onSuccess[] = array($this, 'billingFormSubmitted');
		return $form;
	}

	public function billingFormSubmitted(\Nette\Application\UI\Form $form)
	{
		$values = $form->getValues();
		$data = array(
			'b_name' => \zvitek\Helper::pick($values['b_name']),
			'b_street' => \zvitek\Helper::pick($values['b_street']),
			'b_city' => \zvitek\Helper::pick($values['b_city']),
			'b_zip' => \zvitek\Helper::pick($values['b_zip']),
			'b_ico' => \zvitek\Helper::pick($values['b_ico']),
			'b_dic' => \zvitek\Helper::pick($values['b_dic']),
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
				$this->invalidateControl('billingForm');
			else
				$this->redirect('this');
		}
	}
}