<?php
/**
 * Roles model
 *
 * @author  Copyright (c) 2012 Tomas Marcanik, www.TomasMarcanik.cz
 * @package GUI for Acl
 */

namespace Model\Acl;

/**
 * Roles model
 *
 */
class RolesModel extends ConfigModel
{
    /** @var \Nette\Database\Context */
    private $database;

    /**
     * Contrust Acl BannerModel
     * @param \Nette\Database\Context $database
     */
    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Has parent of node children?
     *
     * @param   integer Parent id
     * @return  integer Number of children
     */
    public function hasChildNodes($parent_id)
    {
        return $this->database->fetchField('SELECT COUNT(*) FROM ' . ConfigModel::TABLE_ROLES . ' WHERE parent_id=?', $parent_id);
    }

    /**
     * Return all children of specific parent of node
     *
     * @param   integer Parent id
     * @return  object
     */
    public function getChildNodes($parent_id)
    {
        return $this->database->fetchAll('SELECT r.id, r.name, r.comment, count(ur.users_id) AS members
			FROM ' . ConfigModel::TABLE_ROLES . ' AS r
			LEFT JOIN ' . ConfigModel::TABLE_USERS_ROLES . ' AS ur ON r.id=ur.acl_roles_id
			WHERE ' . (is_null($parent_id) ? 'r.parent_id IS NULL' : 'r.parent_id=' . $parent_id) . '
			GROUP BY r.id, r.name, r.comment
			ORDER BY r.name;');
    }

    /**
     * Return all roles in the tree structure
     *
     * @return  array
     */
    public function getTreeValues($toArray = FALSE)
    {
        $roles = array();

        if($toArray)
            $roles = $this->getParents(NULL, $roles, 0, TRUE);
        else
            $this->getParents(NULL, $roles, 0);

        return $roles;
    }

    /**
     * All children of specific parent of role placed in a array
     *
     * @param   integer Parent id
     * @param   array Array of curent resources
     * @param   integer Depth of tree structure
     */
    public function getParents($parent_id, &$array, $depth, $toArray = FALSE)
    {
        $rows = $this->database->fetchAll('SELECT id, name, key_name
			FROM ' . ConfigModel::TABLE_ROLES . '
			WHERE ' . (is_null($parent_id) ? 'parent_id IS NULL' : 'parent_id=' . $parent_id) . '
			ORDER BY name');

        $nData = array();
        $i = 0;

        foreach($rows as $row)
        {
            if($toArray)
            {
                $nData[$i]['id'] = $row->id;
                $nData[$i]['name'] = $row->name;
                $nData[$i]['data'] = $this->getParents($row->id, $array, ($depth + 1), TRUE);

                $i++;
            }
            else
            {
                $array[$row->id] = ($depth ? str_repeat("- - ", $depth) : '') . $row->name;
                $this->getParents($row->id, $array, ($depth + 1));
            }
        }

        if($toArray)
            return $nData;
    }

    /**
     * Get role
     *
     * @param integer Role ID
     * @return \Nette\Database\Row
     */
    public function get($id)
    {
        return $this->database->fetch('SELECT id, key_name, parent_id, name, comment FROM ' . ConfigModel::TABLE_ROLES . ' WHERE id=?;', $id);
    }

    /**
     * Insert new role
     * @param $values
     * @return string
     */
    public function insert($values)
    {
        $row = $this->database->table(ConfigModel::TABLE_ROLES)->insert($values);
        return $row->id;
    }

    /**
     * Update role
     *
     * @param innteger Role ID
     * @return array
     */
    public function update($id, $values)
    {
        $this->database->table(ConfigModel::TABLE_ROLES)
        ->where('id', $id)
        ->update($values);
    }

    /**
     * Delete role
     *
     * @param integer Role ID
     */
    public function delete($id)
    {
        $this->database->table(ConfigModel::TABLE_ROLES)->where('id', $id)->delete();
    }

    /**
     * Get all roles of current user
     *
     * @param integer User ID
     * @return array
     */
    public function getAllByUser($userId)
    {
        return $this->database->fetchAll('SELECT r.id, r.name, r.key_name
			FROM ' . ConfigModel::TABLE_ROLES . ' AS r
			JOIN ' . ConfigModel::TABLE_USERS_ROLES . ' AS ur ON r.id=ur.acl_roles_id
			WHERE ur.users_id=?
			ORDER BY r.name;', $userId);
    }

    /**
     * Get all roles of current user
     *
     * @param integer User ID
     * @return array
     */
    public function getAllByUserPairs($userId)
    {
        return $this->database->fetchPairs('SELECT role_id, role_id AS roles FROM ' . ConfigModel::TABLE_USERS_ROLES . ' WHERE user_id=?;', $userId);
    }

    /**
     * Get role name
     *
     * @param integer Role ID
     * @return string
     */
    public function getName($id)
    {
        return $this->database->fetchField('SELECT name FROM ' . ConfigModel::TABLE_ROLES . ' WHERE id=?;', $id);
    }

}
