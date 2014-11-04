<?php
/*
 * Access control list model
 *
 * @date       30. 11. '13
 * @author     Zdenek Vitek <zvitek@iwory.cz>
 * @copyright  iworyStudio
 * @link       http://www.iwory.cz
 * @package    iweCMS
 */

namespace Model\Acl;

class AclModel extends ConfigModel
{
    /** @var \Nette\Database\Context */
    private $database;

    /**
     * Contrust Acl Model
     * @param \Nette\Database\Context $database
     */
    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

	/**
	 * Put in to array parents of specific role
	 * 
	 * @param integer $parent_id ID of parent role
	 * @param string $parent_key Key name of parent role
	 * @param $roles
	 */
    public function getParentRole($parent_id, $parent_key, &$roles)
    {
        $rows = $this->database->table(ConfigModel::TABLE_ROLES)->where('parent_id', $parent_id);

        foreach($rows as $row)
        {
            $roles[] = array('key_name' => $row->key_name, 'parent_key' => $parent_key);
            $this->getParentRole($row->id, $row->key_name, $roles);
        }
    }

    /**
     * Return all roles hierarchically ordered
     *
     * @return  array
     */
    public function getRoles()
    {
        $roles = array();
        $this->getParentRole(NULL, NULL, $roles);
        return $roles;
    }

    /**
     * Put in to array parents of specific resource
     *
     * @param integer ID of parent resource
     * @param string Key name of parent resource
     * @param array Array of all resource
     */
    public function getParentResource($parent_id, $parent_key, &$resources)
    {
        $rows = $this->database->table(ConfigModel::TABLE_RESOURCES)->where('parent_id', $parent_id);

        foreach($rows as $row)
        {
            $resources[] = array('key_name' => $row->key_name, 'parent_key' => $parent_key);
            $this->getParentResource($row->id, $row->key_name, $resources);
        }
    }

    /**
     * Return all resources hierarchically ordered
     *
     * @return  array
     */
    public function getResources()
    {
        $resources = array();
        $this->getParentResource(NULL, NULL, $resources);
        return $resources;
    }

    /**
     * Return all rules of permissions
     *
     * @return  object
     */
    public function getRules()
    {
        $rows = $this->database->fetchAll('
                SELECT
                a.access as access,
                ro.key_name as role,
                re.key_name as resource,
                p.key_name as privilege
                FROM ' . ConfigModel::TABLE_ACL . ' a
                JOIN ' . ConfigModel::TABLE_ROLES . ' ro ON (a.role_id = ro.id)
                LEFT JOIN ' . ConfigModel::TABLE_RESOURCES . ' re ON (a.resource_id = re.id)
                LEFT JOIN ' . ConfigModel::TABLE_PRIVILEGES . ' p ON (a.privilege_id = p.id)
                ORDER BY a.id ASC
        ');
        return $rows;
    }

    /**
     * Get permission
     *
     * @param integer Permission ID
     * @return \Nette\Database\Row
     */
    public function get($id)
    {
        return $this->database->fetch('SELECT * FROM ' . ConfigModel::TABLE_ACL . ' WHERE id=?;', $id);
    }

    /**
     * Get permission with corresponding names
     *
     * @param integer Permission ID
     * @return \Nette\Database\Row
     */
    public function getPermission($id)
    {
        return $this->database->fetch('SELECT a.id, a.access, ro.name AS role, re.name AS resource, p.name AS privilege
			FROM ' . ConfigModel::TABLE_ACL . ' AS a
			LEFT JOIN ' . ConfigModel::TABLE_ROLES . ' AS ro ON a.role_id=ro.id
			LEFT JOIN ' . ConfigModel::TABLE_RESOURCES . ' AS re ON a.resource_id=re.id
			LEFT JOIN ' . ConfigModel::TABLE_PRIVILEGES . ' AS p ON a.privilege_id=p.id
			WHERE a.id=?;', $id);
    }

    /**
     * Insert new permission
     *
     * @param array
     */
    public function insert($values)
    {
        $this->database->table(ConfigModel::TABLE_ACL)->insert($values);
    }

    /**
     * Update permission
     *
     * @param integer Permission ID
     * @param array
     */
    public function update($id, $values)
    {
        $this->database->table(ConfigModel::TABLE_ACL)
        ->where('id', $id)
        ->update($values);
    }

    /**
     * Delete permission
     *
     * @param integer Permission ID
     */
    public function delete($id)
    {
        $this->database->table(ConfigModel::TABLE_ACL)->where('id', $id)->delete();
    }

    /**
     * Get SQL query to permission
     *
     * @return string
     */
    private function getPermissionAllSql()
    {
        return 'SELECT a.id, a.access, ro.name AS role, re.name AS resource, p.name AS privilege
			FROM ' . ConfigModel::TABLE_ACL . ' AS a
			LEFT JOIN ' . ConfigModel::TABLE_ROLES . ' AS ro ON a.role_id=ro.id
			LEFT JOIN ' . ConfigModel::TABLE_RESOURCES . ' AS re ON a.resource_id=re.id
			LEFT JOIN ' . ConfigModel::TABLE_PRIVILEGES . ' AS p ON a.privilege_id=p.id
			ORDER BY ro.name';
    }

    /**
     * Get all permissions
     *
     * @param integer Limit
     * @param intege Offset
     * @return array
     */
    public function getPersmissionAll($limit, $offset)
    {
        $sql = $this->getPermissionAllSql();
        $sql .= sprintf(' LIMIT %d OFFSET %d', $limit, $offset);

        return $this->database->fetchAll($sql);
    }

    /**
     * Get count of all permissions
     *
     * @return integer
     */
    public function getPersmissionAllCount()
    {
        $rows = $this->database->fetchAll($this->getPermissionAllSql());
        return count($rows);
    }

}
