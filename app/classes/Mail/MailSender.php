<?php
/**
 * Mail Sender
 *
 * PHP Version 5.3 (min)
 *
 * @package    DigiTisk
 * @subpackage Classes
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace classes\Mail;

class MailSender extends \Nette\Application\UI\Control
{

	/** @var array */
	public $recipients = array();

	/** @var array */
	public $sender = array();

	/** @var string */
	public $subject;

	/** @var string */
	public $body;

	/** @var array */
	public $recipientsBcc = array();

	/** @var array */
	public $attachment = array();

	/** @var bool */
	public $isBodyHtmlString = TRUE;

	public function __construct()
	{
		parent::__construct();
	}

	public function send()
	{
		$mail = new \Nette\Mail\Message();

		if(count($this->sender) > 1)
			$mail->setFrom($this->sender[0], $this->sender[1]);
		else
			$mail->setFrom($this->sender[0]);

		$mail->setSubject($this->subject);

		if(count($this->recipients))
		{
			foreach($this->recipients as $recipient)
			{
				if(is_array($recipient))
				{
					if(\Nette\Utils\Validators::isEmail($recipient[0]))
					{
						$mail->addTo($recipient[0], $recipient[1]);
					}
				}
				else
				{
					if(\Nette\Utils\Validators::isEmail($recipient))
					{
						$mail->addTo($recipient);
					}
				}
			}
		}
		else
			return FALSE;

		if(count($this->recipientsBcc))
		{
			foreach($this->recipientsBcc as $recipientsBcc)
			{
				if(is_array($recipientsBcc))
				{
					if(\Nette\Utils\Validators::isEmail($recipientsBcc[0]))
					{
						$mail->addBcc($recipientsBcc[0], $recipientsBcc[1]);
					}
				}
				else
				{
					if(\Nette\Utils\Validators::isEmail($recipientsBcc))
					{
						$mail->addBcc($recipientsBcc);
					}
				}
			}
		}

		if(count($this->attachment))
		{
			foreach($this->attachment as $attachment)
			{
				if(is_array($attachment))
				{
					$mail->addAttachment($attachment[0], $attachment[1]);
				}
				else
				{
					$mail->addAttachment($attachment);
				}
			}
		}

		if($this->isBodyHtmlString)
			$mail->setHtmlBody($this->body);
		else
			$mail->setBody(strip_tags($this->body));

		try
		{
			$mailC = new \Nette\Mail\SendmailMailer();
			$mailC->send($mail);

			return TRUE;
		}
		catch(Exception $e)
		{
			return FALSE;
		}
	}
}