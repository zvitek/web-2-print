<?php
/*
 * @date       3. 11. '13
 * @author     Zdenek Vitek <zvitek@iwory.cz>
 * @copyright  iworyStudio
 * @link       http://www.iwory.cz
 */

namespace Model\Acl;

class UserServis extends ConfigModel
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

    /*
     * Load all data from table iwe_users
     * @return (obj)
     */
    public function data_getUsers()
    {
        return $this->database->table('is_users');
    }

    /**
     * Get all users
     *
     * @param string Seach string
     * @return \Nette\Database\Table\Selection
     */
    public function getAll($search)
    {
        $sql = $this->database->table('is_users')
        ->select('id, name')
        ->order('name');
        if(!empty($search))
        {
            $sql->where('name LIKE ?', $search);
        }

        return $sql;
    }

    /**
     * Get all users belongs to role
     *
     * @param integer Role ID
     * @return array
     */
    public function getAllByRole($roleId)
    {
        return $this->database->fetchAll('SELECT u.id, u.name, u.surname, u.username
			FROM ' . ConfigModel::TABLE_USERS . ' AS u
			LEFT JOIN ' . ConfigModel::TABLE_USERS_ROLES . ' AS r ON u.id=r.user_id
			WHERE r.role_id=? AND name IS NOT NULL
			ORDER BY u.name;', $roleId);
    }

    /**
     * Get user name
     *
     * @param integer User ID
     * @return string
     */
    public function getName($userId)
    {
        return $this->database->fetchColumn('SELECT name FROM ' . ConfigModel::TABLE_USERS . ' WHERE id=?;', $userId);
    }

    /**
     * Insert new user
     *
     * @param array
     */
    public function insert($values)
    {
        $this->database->table(ConfigModel::TABLE_USERS)->insert($values);
    }

    /**
     * Insert relation between user and role
     *
     * @param array
     */
    public function insertUserRole($values)
    {
        $this->database->table(ConfigModel::TABLE_USERS_ROLES)->insert($values);
    }

    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @return integer
     */
    public function lastInsertId()
    {
        if($this->database->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'pgsql')
        {
            return $this->database->lastInsertId('gui_users_id_seq');
        }
        else
        {
            return $this->database->lastInsertId();
        }
    }

    /**
     * Update user
     *
     * @param integer User ID
     * @param array
     */
    public function update($userId, $values)
    {
        $this->database->table(ConfigModel::TABLE_USERS)
        ->where('id', $userId)
        ->update($values);
    }

    /**
     * Delete user
     *
     * @param integer User ID
     */
    public function delete($id)
    {
        $this->database->table(ConfigModel::TABLE_USERS)->where('id', $id)->delete();
    }

    /**
     * Delete relation between user and role
     *
     * @param integer User ID
     */
    public function deleteUserRole($id)
    {
        $this->database->table(ConfigModel::TABLE_USERS_ROLES)->where('user_id', $id)->delete();
    }

}
