<?php
/**
 * Activation Mail Generator/Sender
 *
 * PHP Version 5.3 (min)
 *
 * @package    DigiTisk
 * @subpackage Classes
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */
namespace classes\Mail\ActivationMails;

class ActivationMailGenerator extends \Nette\Application\UI\Control
{
	/** @var \Nette\ComponentModel\IContainer */
	private $config;

	/** @var array */
	public $data;

	/** @var string */
	public $type = NULL;

	public function __construct($config)
	{
		parent::__construct();
		$this->config = $config;
	}

	/**
	 * Odešle aktivační email
	 * Před zavoláním téhle funkce je zapotřebí naplnit
	 * proměnnou $data komponenty. Ta musí obsahovat nasledovní klíče:
	 * $data['email'] - email, na který chceme mail odeslat
	 * $data['name'] - jméno, které se doplní k adrese
	 * Proměnná $data pak musí ještě obsahovat data potřebná pro šablonu mailu.
	 * @return bool
	 * @return bool
	 */
	public function sendActivationEmail()
	{
		$this->template->data = $this->data;

		$mailSender = new \classes\Mail\MailSender();
		$mailSender->recipients[] = array($this->data['email'], $this->data['name']);
		$mailSender->sender[] = $this->config['mail']['parameters']['registration'][strtolower($this->type)]['sender'];
		$mailSender->subject = $this->config['mail']['parameters']['registration'][strtolower($this->type)]['subject'];
		$mailSender->body = $this->template;

		return $mailSender->send() ? TRUE : FALSE;
	}

	/**
	 * Připraví šablonu mailu
	 *
	 * @param null $class
	 * @return \Nette\Application\UI\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$templateName = $this->config['path']['mailTemplates'];
		$template = parent::createTemplate($class);

		$template->_presenter = parent::getPresenter(TRUE);

		if(file_exists($templateName . '-' . ucfirst($this->type) . '.latte'))
			$templateName .= 'RegistrationMail-' . ucfirst($this->type) . '.latte';
		else
			$templateName .= 'RegistrationMail.latte';

		$template->setFile($templateName);
		return $template;
	}
}
