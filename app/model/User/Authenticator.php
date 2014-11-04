<?php

namespace App\Model\User;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;

class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator
{
	const
		TABLE_NAME = 'users',
		TABLE_USERS_ROLES = 'acl_users_roles',
		TABLE_ACL_ROLES = 'acl_roles',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'email',
		COLUMN_PASSWORD_HASH = 'password';


	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update(array(
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
			));
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH]);
		return new Nette\Security\Identity($row[self::COLUMN_ID], $this->getUsersRoles($row[self::COLUMN_ID]), $arr);
	}


	/**
	 * Adds new user.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function add($username, $password)
	{
		$this->database->table(self::TABLE_NAME)->insert(array(
			self::COLUMN_NAME => $username,
			self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
		));
	}

	public function getUsersRoles($usersID = NULL)
	{
		$pairedRoles = array();
		if(is_null($usersID))
			return $pairedRoles;

		$roles = $this->database->table(self::TABLE_USERS_ROLES)->where('users_id', $usersID)->fetchAll();
		if(!count($roles))
			return $pairedRoles;

		foreach($roles as $role)
		{
			$pairedRoles[$role[self::TABLE_ACL_ROLES]['id']] = $role[self::TABLE_ACL_ROLES]['key_name'];
		}

		return $pairedRoles;
	}

}
