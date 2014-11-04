<?php
/**
 * Resources model
 *
 * @author  Copyright (c) 2012 Tomas Marcanik, www.TomasMarcanik.cz
 * @package GUI for Acl
 */

namespace Model\Acl;

/**
 * Resources model
 *
 */
class ResourcesModel extends ConfigModel
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

    /* Has parent of node children?
     *
     * @param integer Parent id
     * @return integer Number of children
     */
    public function hasChildNodes($parent_id)
    {
        return $this->database->fetchField('SELECT COUNT(*) FROM ' . ConfigModel::TABLE_RESOURCES . ' WHERE parent_id=?', $parent_id);
    }

    /**
     * Return all children of specific parent of node
     *
     * @param   integer Parent id
     * @return  object
     */
    public function getChildNodes($parent_id)
    {
        return $this->database->fetchAll('SELECT id, name, comment
			FROM ' . ConfigModel::TABLE_RESOURCES . '
			WHERE ' . (is_null($parent_id) ? 'parent_id IS NULL' : 'parent_id=' . $parent_id) . '
			ORDER BY name;');
    }

    /**
     * Return all resources in the tree structure
     *
     * @return  array
     */
    public function getTreeValues($toArray = FALSE)
    {
        $resources = array();

        if($toArray)
            $resources = $this->getParents(NULL, $resources, 0, TRUE);
        else
            $this->getParents(NULL, $resources, 0);

        return $resources;
    }

    /**
     * All children of specific parent of resources placed in a array
     *
     * @param   integer Parent id
     * @param   array Array of curent resources
     * @param   integer Depth of tree structure
     */
    public function getParents($parent_id, &$array, $depth, $toArray = FALSE)
    {
        $rows = $this->database->fetchAll('SELECT id, name, key_name
			FROM ' . ConfigModel::TABLE_RESOURCES . '
			WHERE ' . (is_null($parent_id) ? 'parent_id IS NULL' : 'parent_id=' . $parent_id) . '
			ORDER BY name;');

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
                $array[$row->id] = ($depth ? str_repeat('- ', $depth) : '') . $row->name;
                $this->getParents($row->id, $array, ($depth + 1));
            }
        }

        if($toArray)
            return $nData;
    }

    /**
     * Get resource
     *
     * @param integer Resource ID
     * @return \Nette\Database\Row
     */
    public function get($id)
    {
        return $this->database->fetch('SELECT key_name, parent_id, name, comment FROM ' . ConfigModel::TABLE_RESOURCES . ' WHERE id=?;', $id);
    }

    /**
     * Insert new resource
     *
     * @param array
     */
    public function insert($values)
    {
        $this->database->table(ConfigModel::TABLE_RESOURCES)->insert($values);
        return $this->database->getInsertId();
    }

    /**
     * Update resource
     *
     * @param integer Resource ID
     * @param array
     */
    public function update($id, $values)
    {
        $this->database->table(ConfigModel::TABLE_RESOURCES)
        ->where('id', $id)
        ->update($values);
    }

    /**
     * Delete resource
     *
     * @param integer Resource ID
     */
    public function delete($id)
    {
        $this->database->table(ConfigModel::TABLE_RESOURCES)->where('id', $id)->delete();
    }

    /**
     * Get resource
     *
     * @param integer Resource ID
     * @return string
     */
    public function getName($id)
    {
        return $this->database->fetchField('SELECT name FROM ' . ConfigModel::TABLE_RESOURCES . ' WHERE id=?;', $id);
    }

}
