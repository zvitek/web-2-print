<?php
/*
 * Permission model of access control list
 *
 * @date       30. 11. '13
 * @author     Zdenek Vitek <zvitek@iwory.cz>
 * @copyright  iworyStudio
 * @link       http://www.iwory.cz
 * @package    iweCMS
 */

namespace Model\Acl;
use \Nette\Database\Context;

class PermissionModel extends \Nette\Security\Permission
{
    public function __construct(Context $connection)
    {
        $model = new AclModel($connection);

        $roles = $model->getRoles();

        foreach($roles as $role)
            $this->addRole($role['key_name'], $role['parent_key']);

        $resources = $model->getResources();

        foreach($resources as $resource)
            $this->addResource($resource['key_name'], $resource['parent_key']);

        $rules = $model->getRules();

        foreach($rules as $rule)
            $this->{$rule->access ? 'allow' : 'deny'}($rule->role, $rule->resource, $rule->privilege);
    }

}
