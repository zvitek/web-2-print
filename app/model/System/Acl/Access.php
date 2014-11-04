<?php
/*
 * Access model
 *
 * @date       30. 11. '13
 * @author     Zdenek Vitek <zvitek@iwory.cz>
 * @copyright  iworyStudio
 * @link       http://www.iwory.cz
 * @package    iweCMS
 */

namespace Model\Acl;

class AccessModel extends ConfigModel
{
    /** @var array */
    private $access = array();

    /** @var \Nette\Database\Context */
    private $database;

    /**
     * @param array Array of roles
     */
    public function __construct(\Nette\Database\Context $connection, $roles)
    {
        $this->database = $connection;

        $resources = $this->database->table(ConfigModel::TABLE_RESOURCES)->order('name');
        $privileges = $this->database->table(ConfigModel::TABLE_PRIVILEGES)->order('name');

        $acl = new PermissionModel($this->database);
        $i = 0;
        foreach($resources as $res)
        {
            foreach($privileges as $pri)
            {
                foreach($roles as $role)
                {
                    if($acl->isAllowed($role->key_name, $res->key_name, $pri->key_name))
                    {
                        $this->access[$i]['resource'] = $res->name;
                        $this->access[$i]['privileg'] = $pri->name;
                        $i++;
                        break 1;
                    }
                }
            }
        }
    }

    /**
     * @return array Resources and privileges for current roles
     */
    public function getAccess()
    {
        return $this->access;
    }

}
