<?php

/**
 * Profile Control
 * PHP Version 5.3 (min)
 * @package    DigiTisk
 * @subpackage All
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace App\User\control;

class MerchantEditFormControl extends \Nette\Application\UI\Control
{
	/** @var \App\Model\User\UserService */
	private $userService;

	/** @var \App\Model\Merchant\MerchantService */
	private $merchantService;

	/** @var \Nette\Security\User */
	private $user;

	/** @var array */
	private $config;

	/** @var int */
	private $roles;

	public function __construct(\App\Model\User\UserService $userService, \App\Model\Merchant\MerchantService $merchantService, \Nette\Security\User $user, $config)
	{
		$this->userService = $userService;
		$this->merchantService = $merchantService;
		$this->user = $user;
		$this->config = $config;

		$this->roles = $this->user->roles;
	}

	public function render()
	{
		$this->template->setFile(\zvitek\Helper::control_template(__DIR__, 'MerchantEditForm'));
		$this->setDefaults();
		$this->template->roles = $this->roles;
		$this->template->render();
	}

	public function setDefaults()
	{
		if(in_array('merchant', $this->roles))
		{
//			$merchantData = $this->merchantService->table_merchant(array($this->user->id), array(), TRUE);

//			$this['merchantForm']->setDefaults($merchantData);
		}
	}

	public function createComponentMerchantForm()
	{
		$form = new \classes\Forms\BaseForm();

		$form->addText('name', 'Jméno a příjmení');
		$form->addText('street', 'Ulice');
		$form->addText('city', 'Město');
		$form->addText('zip', 'PSČ');
		$form->addText('ico', 'IČ');
		$form->addText('dic', 'DIČ');
		$form->addText('system_url', 'Systémová URL');

		// RULES
		$form->zip_rules($form['zip']);
		$form->ico_rules($form['ico']);
		$form->dic_rules($form['dic']);
		$form->url_rules($form['system_url']);

		$form->addSubmit('modify', 'Upravit');
		$form->onSuccess[] = array($this, 'merchantFormSubmitted');
		return $form;
	}

	public function merchantFormSubmitted(\Nette\Application\UI\Form $form)
	{
		$values = $form->getValues();
		$data = array(
			'name' => \zvitek\Helper::pick($values['name']),
			'street' => \zvitek\Helper::pick($values['street']),
			'city' => \zvitek\Helper::pick($values['city']),
			'zip' => \zvitek\Helper::pick($values['zip']),
			'ico' => \zvitek\Helper::pick($values['ico']),
			'dic' => \zvitek\Helper::pick($values['dic']),
			'system_url' => \zvitek\Helper::pick($values['system_url']),
		);

		$data = \zvitek\Helper::clearEmptyArray($data);
		\Tracy\Debugger::dump($data);
		die;

		if(TRUE)
		{
			$this->getPresenter()->flashMessage('Litujeme, tahle služba je dočasně nepřístupná', 'error');
			$this->getPresenter('this');
		}
		else
		{
			if(!in_array('merchant', $this->roles))
			{
				$this->getPresenter()->flashMessage('Na upravování těhle informací nemáte dostatečné oprávnění', 'error');
				$this->getPresenter('this');
			}
			else
			{
				if($this->merchantService->i_control($data, $this->user->id))
					$this->getPresenter()->flashMessage('Položky byly upraveny', 'success');
				else
					$this->getPresenter()->flashMessage('Položky se nezdařilo upravit', 'error');

				if($this->getPresenter()->isAjax())
					$this->invalidateControl('merchantForm');
				else
					$this->redirect('this');
			}
		}
	}

	public function verifyIC($item)
	{
		return \zvitek\Helper::verifyIC($item->value);
	}

	public function verifyDIC($item)
	{
		return \zvitek\Helper::verifyDIC($item->value);
	}
}