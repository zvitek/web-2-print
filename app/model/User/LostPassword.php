<?php
/**
 * Lost Password Model
 * PHP Version 5.3 (min)
 * @package    DigiTisk
 * @subpackage User
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace App\Model\User;

class LostPasswordService extends \Nette\Object
{
	/** @var \Nette\Database\Context */
	private $database;

	/** @var  array */
	private $config;

	public function __construct(\Nette\Database\Context $database)
	{
		$this->database = $database;
		$this->config = \Nette\Environment::getContext()->parameters;
	}

	public function getDatabase()
	{
		return $this->database;
	}


	/**************************************************************************************************************z*v*/
	/********** LOST PASSWORDS FCN **********/

	/**
	 * Vloží / aktualizuje dáta zákazníka (pro tabulku ztracených hesel) (aktualizace jestli je zadáno $userID)
	 * Pracuje s tabulkami: users_lost_password
	 * @param array    $data
	 * @param null|int $lineID
	 * @return bool
	 */
	public function i_userLostPassword($data, $lineID = NULL)
	{
		try
		{
			if(!is_null($lineID))
			{
				$this->table_usersLostPasswords()->where('id', $lineID)->update($data);
				return TRUE;
			}
			else
			{
				$row = $this->table_usersLostPasswords()->insert($data);
				return $row->id;
			}
		}
		catch(PDOException $e)
		{
			return FALSE;
		}
	}

	/**
	 * Nastaví všechny položky se stracenýmy heslama na expirována
	 * Pracuje s tabulkami: users_lost_password
	 * @param int $userID
	 * @return bool
	 */
	public function user_expireAllLostPasswords($userID)
	{
		if(!\Nette\Utils\Validators::isNumericInt($userID))
			return FALSE;

		try
		{
			$this->table_usersLostPasswords()->where('users_id', $userID)->update(array('valid' => 0));
			return TRUE;
		}
		catch(PDOException $e)
		{
			return FALSE;
		}
	}

	/**
	 * Najde záznam v tabulce ztracených hesel
	 * Pracuje s tabulkami: users_lost_passwords
	 * @param int  $userID
	 * @param bool $onlyValid
	 * @return bool|mixed|\Nette\Database\Table\IRow
	 */
	public function data_usersLostPasswords($userID, $onlyValid = FALSE)
	{
		if(!\Nette\Utils\Validators::isNumericInt($userID))
			return FALSE;

		$rows = $this->table_usersLostPasswords()->where(array('users_id' => $userID));

		if($onlyValid)
			return $rows->where(array('valid' => 1))->fetch();
		else
			return $rows->fetchAll();
	}

	/**
	 * Nastaví stracené heslo a vygeneruje nové
	 * Pracuje s tabulkami: users, lost_passwords
	 * @param int $userID
	 * @return bool|string
	 */
	public function user_setLostPassword($userID)
	{
		if(!\Nette\Utils\Validators::isNumericInt($userID))
			return FALSE;

		$user = $this->data_user(array($userID), array(), TRUE);
		if(!count($user)) return FALSE;

		$newToken = \Nette\Utils\Random::generate(35, '0-9a-zA-Z');

		$lostPasswordData = array(
			'users_id' => $user['id'],
			'token' => $newToken,
			'valid' => 1,
			'expire' => new \Nette\Utils\DateTime('NOW + 24 hours'),
			'created' => new \Nette\Utils\DateTime(),
		);

		$checkIfExist = $this->data_usersLostPasswords($userID, TRUE);
		if($checkIfExist)
		{
			$this->i_userLostPassword(array('valid' => 0), $checkIfExist['id']);
		}

		if($this->i_userLostPassword($lostPasswordData))
			return $newToken;
		else
			return FALSE;
	}

	/**
	 * Ověří, zda neexpirovala doba na přihlášení pomocí dočasného hesla.
	 * @param string $token
	 * @return bool
	 */
	public function checkExpirationLostPassword($token)
	{
		$user = $this->userLostPassword_findByToken($token);
		if($user)
		{
			$lostPassword = $this->data_usersLostPasswords($user['id'], TRUE);
			if($lostPassword)
			{
				$now = new \Nette\Utils\DateTime();
				if($lostPassword['expire'] <= $now)
				{
					$this->user_expireAllLostPasswords($user['id']);
					return FALSE;
				}
				return TRUE;
			}
		}
		return FALSE;
	}

	public function userLostPassword_findByToken($token)
	{
		if(!\Nette\Utils\Validators::isUnicode($token) || strlen((string)$token) !== $this->config['user']['tokenLength'])
			return FALSE;

		return $this->table_usersLostPasswords()->where(array('token' => $token))->fetch();
	}

	public function revokePassword($token) {
		if(!$this->checkExpirationLostPassword($token))
		{

		}
	}


	public function table_usersLostPasswords()
	{
		return $this->database->table(Config::TABLE_USERS_LOST_PASSWORDS);
	}
}