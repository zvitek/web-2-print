<?php
/**
 * User Model
 * PHP Version 5.3 (min)
 * @package    DigiTisk
 * @subpackage User
 * @author     Zdeněk Vítek <zvitek@iwory.cz>
 */

namespace App\Model\User;

class UserService extends \Nette\Object
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
	/********** VERIFY FCN **********/

	/**
	 * Zjistí zda je e-mail již registrovaný jako zákazník
	 * @param string $email
	 * @return bool
	 */
	public function verify_freeEmail($email)
	{
		return $this->user_findByEmail($email) ? TRUE : FALSE;
	}

	/**************************************************************************************************************z*v*/
	/********** USER FCN **********/

	/**
	 * Najde zákazníka podle tokenu (35 znaků)
	 * Pracuje s tabulkami: users
	 * @param string $token
	 * @return bool|mixed|\Nette\Database\Table\IRow
	 */
	public function user_findByToken($token)
	{
		if(strlen((string)$token) !== $this->config['user']['tokenLength'])
			return FALSE;

		return $this->table_users()->where(array('token' => $token))->fetch();
	}

	/**
	 * Najde zákazníka podle emailu
	 * Pracuje s tabulkami: users
	 * @param string $email
	 * @return bool|mixed|\Nette\Database\Table\IRow
	 */
	public function user_findByEmail($email)
	{
		if(!\Nette\Utils\Validators::isEmail($email))
			return FALSE;

		return $this->table_users()->where(array('email' => $email))->fetch();
	}

	/**************************************************************************************************************z*v*/
	/********** DATA FCN **********/

	/**
	 * Aktivuje účet s daným id
	 * @param int $userID
	 * @return bool
	 */
	public function user_activateAccount($userID)
	{
		if(!\Nette\Utils\Validators::isNumericInt($userID))
			return FALSE;
		return $this->i_user(array('active' => new \Nette\Utils\DateTime()), $userID);
	}

	/**
	 * Vloží / aktualizuje dáta zákazníka (aktualizace jestli je zadáno $userID)
	 * Pracuje s tabulkami: users
	 * @param array    $data
	 * @param null|int $userID
	 * @return bool
	 */
	public function i_user($data, $userID = NULL)
	{
		try
		{
			if(!is_null($userID))
			{
				$this->table_users()->where('id', $userID)->update($data);
				return TRUE;
			}
			else
			{
				$row = $this->table_users()->insert($data);
				return $row->id;
			}
		}
		catch(PDOException $e)
		{
			return FALSE;
		}
	}

	/**
	 * Funkce vrací struktuovaé data o zákazníkovy (jednom nebo více)
	 * Pracuje s tabulkami: users
	 * $users - pole idček zákazníkú
	 * $columns - seznam polí, které chceme vrátit - když je to prázdný array vrátí se všchny
	 * - možnosti:
	 * -- name
	 * -- email
	 * -- phone
	 * -- token
	 * -- created
	 * -- billing
	 * -- transport
	 * $fetch - vrátí jeden row
	 * @param array $users
	 * @param array $columns
	 * @param bool  $fetch
	 * @return array
	 */
	public function data_user(array $users, array $columns = array(), $fetch = FALSE)
	{
		$data = array();
		$columns = \zvitek\Helper::array_clear($columns);
		$get_all = count($columns);

		$userData = $this->table_users()->where('id IN', $users)->order('created ASC');

		if($userData->count())
		{
			foreach($userData as $user)
			{
				$c['id'] = $user->id;

				if(!$get_all || in_array('name', $columns))
					$c['name'] = $user['name'];

				if(!$get_all || in_array('email', $columns))
					$c['email'] = $user['email'];

				if(!$get_all || in_array('password', $columns))
					$c['password'] = $user['password'];

				if(!$get_all || in_array('phone', $columns))
					$c['phone'] = $user['phone'];

				if(!$get_all || in_array('token', $columns))
					$c['token'] = $user['token'];

				if(!$get_all || in_array('created', $columns))
					$c['created'] = $user['created'];

				if(!$get_all || in_array('billing', $columns))
				{
					$c['billing'] = array(
						'b_name' => $user['b_name'],
						'b_street' => $user['b_street'],
						'b_city' => $user['b_city'],
						'b_zip' => $user['b_zip'],
						'b_ico' => $user['b_ico'],
						'b_dic' => $user['b_dic'],
					);
				}

				if(!$get_all || in_array('transport', $columns))
				{
					$c['transport'] = array(
						't_name' => $user['t_name'],
						't_street' => $user['t_street'],
						't_city' => $user['t_name'],
						't_zip' => $user['t_name'],
					);
				}

				if($fetch)
					return $c;

				$data[$c['id']] = $c;
			}
		}

		return $data;
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
	 * @param int  $id
	 * @param bool $onlyValid
	 * @return bool|mixed|\Nette\Database\Table\IRow
	 */
	public function data_usersLostPasswords($id, $onlyValid = FALSE)
	{
		if(!\Nette\Utils\Validators::isNumericInt($id))
			return FALSE;

		$rows = $this->table_usersLostPasswords()->where(array('id' => $id));

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
					$this->user_expireAllLostPasswords($user['users_id']);
					return FALSE;
				}
				return $user['users_id'];
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


	/**************************************************************************************************************z*v*/
	/********** ACL ROLES FCN **********/

	/**
	 * Vloží roli pro zákazníka
	 * @param int $userID
	 * @param int $roleID
	 * @return bool
	 */
	public function i_userRoles($userID, $roleID)
	{
		try
		{
			$data = array('users_id' => $userID, 'acl_roles_id' => $roleID);
			$this->table_usersRole()->where($data)->delete();

			$this->table_usersRole()->insert($data);
			return TRUE;
		}
		catch(PDOException $e)
		{
			return FALSE;
		}
	}

	/**************************************************************************************************************z*v*/
	/********** INIT TABLES **********/

	public function table_users()
	{
		return $this->database->table(Config::TABLE_USERS);
	}

	public function table_usersRole()
	{
		return $this->database->table(Config::TABLE_USER_ROLES);
	}

	public function table_usersLostPasswords()
	{
		return $this->database->table(Config::TABLE_USERS_LOST_PASSWORDS);
	}
}