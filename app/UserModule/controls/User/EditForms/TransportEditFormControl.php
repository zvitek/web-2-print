<?php

/**
 * Profile Control
 * PHP Version 5.3 (min)
 * @package    DigiTisk
 * @subpackage All
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace App\User\control;

class TransportEditFormControl extends \Nette\Application\UI\Control
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
		$this->template->setFile(\zvitek\Helper::control_template(__DIR__,'TransportEditForm'));
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
				$this['transportForm']->setDefaults($userData['transport']);
		}
	}

	public function createComponentTransportForm()
	{
		$form = new \classes\Forms\BaseForm();

		$form->addText('t_name', 'Jméno a příjmení');
		$form->addText('t_street', 'Ulice');
		$form->addText('t_city', 'Město');
		$form->addText('t_zip', 'PSČ');

		//RULES
		$form->zip_rules($form['t_zip']);

		$form->addSubmit('modify', 'Upravit');
		$form->onSuccess[] = array($this, 'transportFormSubmitted');
		return $form;
	}

	public function transportFormSubmitted(\Nette\Application\UI\Form $form)
	{
		$values = $form->getValues();
		$data = array(
			't_name' => \zvitek\Helper::pick($values['t_name']),
			't_street' => \zvitek\Helper::pick($values['t_street']),
			't_city' => \zvitek\Helper::pick($values['t_city']),
			't_zip' => \zvitek\Helper::pick($values['t_zip']),
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
				$this->invalidateControl('transportForm');
			else
				$this->redirect('this');
		}
	}
}