<?php
namespace classes\Forms;

use Nette;
use Nette\Application\UI;
use Nette\Forms;

class BaseForm extends UI\Form
{

	public function __construct()
	{
		parent::__construct();
	}

	public function addTo($form)
	{
		foreach($form->getControls() as $control)
		{
			$this->addComponent(clone $control, $control->getName());
		}
	}

	/**
	 * @param \Nette\Forms\Controls\TextInput $control
	 * @return \Nette\Forms\Rules
	 */
	public function condition_needed(Forms\Controls\TextInput $control, $needed)
	{
		if(!$needed)
			return $control->addCondition(\Nette\Application\UI\Form::FILLED);
		else
			return $control;
	}

	/**
	 * @param Forms\Controls\TextInput $control
	 */
	public function zip_rules(Forms\Controls\TextInput $control, $needed = FALSE)
	{
		$this->condition_needed($control, $needed)
			->addRule(\Nette\Application\UI\Form::INTEGER, 'PSČ musí být číslo')
			->addRule(\Nette\Application\UI\Form::LENGTH, 'PSČ musí být 5 místné číslo', 5);
	}

	/**
	 * @param \Nette\Forms\Controls\TextInput $control
	 */
	public function ico_rules(Forms\Controls\TextInput $control, $needed = FALSE)
	{
		$this->condition_needed($control, $needed)
			->addRule(array($this, 'verifyIC'), 'IČ není ve správném tvaru');
	}

	/**
	 * @param \Nette\Forms\Controls\TextInput $control
	 */
	public function dic_rules(Forms\Controls\TextInput $control, $needed = FALSE)
	{
		$this->condition_needed($control, $needed)
			->addRule(array($this, 'verifyDIC'), 'DIČ není ve správném tvaru');
	}

	/**
	 * @param \Nette\Forms\Controls\TextInput $control
	 */
	public function phone_rules(Forms\Controls\TextInput $control, $needed = FALSE)
	{
		$this->condition_needed($control, $needed)
			->addRule(\Nette\Application\UI\Form::INTEGER, 'Telefon musí být číslo')
			->addRule(\Nette\Application\UI\Form::LENGTH, 'Telefon musí být 9 místné číslo', 9);
	}

	public function url_rules(Forms\Controls\TextInput $control, $needed = FALSE)
	{
		$this->condition_needed($control, $needed)
			->addRule(array($this, 'verifyURL'), 'URL není ve správném tvaru');
	}

	public function verifyIC($item)
	{
		return \zvitek\Helper::verifyIC($item->value);
	}

	public function verifyDIC($item)
	{
		return \zvitek\Helper::verifyDIC($item->value);
	}

	public function verifyURL($item)
	{
		return Nette\Utils\Validators::isUrl($item->value) ? TRUE : FALSE;
	}
}