<?php
/**
 * Privileges model
 *
 * @author  Copyright (c) 2012 Tomas Marcanik, www.TomasMarcanik.cz
 * @package GUI for Acl
 */

namespace Model\Acl;

/**
 * Privileges model
 *
 */
class PrivilegesModel extends ConfigModel
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
     * Get all privileges
     *
     * @return array
     */
    public function getAll()
    {
        return $this->database->fetchAll('SELECT id, name, comment FROM ' . ConfigModel::TABLE_PRIVILEGES . ' ORDER BY name;');
    }

    /**
     * Get privileg
     *
     * @param integer Privileg ID
     * @return \Nette\Database\Row
     */
    public function get($id)
    {
        return $this->database->fetch('SELECT key_name, name, comment FROM ' . ConfigModel::TABLE_PRIVILEGES . ' WHERE id=?;', $id);
    }

    /**
     * Inseert new privileg
     *
     * @param array
     */
    public function insert($values)
    {
        $row = $this->database->table(ConfigModel::TABLE_PRIVILEGES)->insert($values);
        return $row->id;
    }

    /**
     * Update privileg
     *
     * @param integer Privileg ID
     * @return array
     */
    public function update($id, $values)
    {
        $this->database->table(ConfigModel::TABLE_PRIVILEGES)
        ->where('id', $id)
        ->update($values);
    }

    /**
     * Delete privileg
     *
     * @param integer Privileg ID
     */
    public function delete($id)
    {
        $this->database->table(ConfigModel::TABLE_PRIVILEGES)->where('id', $id)->delete();
    }

    /**
     * Get privileg name
     *
     * @param integer Privileg ID
     * @return string
     */
    public function getName($id)
    {
        return $this->database->fetchColumn('SELECT name FROM ' . ConfigModel::TABLE_PRIVILEGES . ' WHERE id=?;', $id);
    }

}
