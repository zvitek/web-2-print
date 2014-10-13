<?php
/**
 * Customer Model Config
 *
 * PHP Version 5.3 (min)
 *
 * @package DigiTisk
 * @subpackage Customer
 * @author Zdeněk Vítek <zvitek@iwory.cz>
 */
namespace App\Model\User;

use Nette\Object;

class Config extends Object {

    /** db table constants */
    const
    TABLE_USERS = 'users',
	TABLE_USER_ROLES = 'acl_users_roles',
	TABLE_USER_ROLES_KEYS = 'acl_roles',
	TABLE_USERS_LOST_PASSWORDS = 'lost_passwords';
}