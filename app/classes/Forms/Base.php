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
}