<?php
/**
 * Config model
 *
 * @date       30. 11. '13
 * @author     Zdenek Vitek <zvitek@iwory.cz>
 * @copyright  iworyStudio
 * @link       http://www.iwory.cz
 */

namespace Model\Acl;

class ConfigModel extends \Nette\Object
{
    /** constants */
    const
    TABLE_ACL = 'acl',
    TABLE_PRIVILEGES = 'acl_privileges',
    TABLE_RESOURCES = 'acl_resources',
    TABLE_ROLES = 'acl_roles',
    TABLE_USERS = 'users',
    TABLE_USERS_ACCESS_TOKEN = 'users_access_token',
    TABLE_USERS_UNAUTHORIZED = 'users_unauthorized_access',
    TABLE_USERS_ROLES = 'acl_users_roles',
    TABLE_NEWSLETTER = 'newsletter',
    TABLE_LOG_ON = 'users_log_on',
    TABLE_USERS_LOG = 'users_log';
}
