<?php

namespace App\SystemModule\AclModule\Presenters;

use \Nette\Application\UI\Form;
use \Nette\Caching\Cache;

class PermissionPresenter extends \App\SystemModule\BasePresenter
{
    /* @var Cache */
    private $cache;

    /** @var int */
    private $id;

    /* @var bool */
    protected $isEditPermission = FALSE;

    /* @var array */
    protected $resourcesDataTree;

    /* @var array */
    protected $roleDataTree;

    /* @var array */
    protected $privilegesDataTree;

    public function startup()
    {
        parent::startup();
        $this->cache = new Cache($this->context->cacheStorage, $this->config['acl']['namespace']);
    }

    public function renderDefault()
    {
        $vp = new \VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;

        $paginator->itemCount = $this->context->AclModel->getPersmissionAllCount();
        $permissions = $this->context->AclModel->getPersmissionAll($paginator->itemsPerPage, $paginator->offset);

        $this->template->acl = $permissions;
    }

    public function actionDetail($id = NULL)
    {
        $this->isEditPermission = is_null($id) ? FALSE : TRUE;

        if($this->isEditPermission)
            $this->id = $id;

        $this->roleDataTree = $this->context->AclRolesModel->getTreeValues(TRUE);
        $this->resourcesDataTree = $this->context->AclResourcesModel->getTreeValues(TRUE);
        $this->privilegesDataTree = $this->context->AclPrivilegesModel->getAll();

        if($this->isEditPermission)
        {
            $data = $this->context->AclModel->get($id);
            if($data)
            {
                $form = $this->getComponent('addEdit');
                $data->privilege_id = (int) $data->privilege_id;
                $data->resource_id = (int) $data->resource_id;
                $data->access = (int) $data->access;
                $form->setDefaults($data);
                $this->template->form = $form;
            }
            else
            {
                $this->flashMessage('Přístup nenalezen!', 'error autoclose');
                $this->redirect('Permission:');
            }
        }

        $this->template->isEditPermission = $this->isEditPermission;
        $this->template->dataTreeResources = $this->resourcesDataTree;
        $this->template->dataTreeRole = $this->roleDataTree;
        $this->template->dataTreePrivileges = $this->privilegesDataTree;
    }

    protected function createComponentAddEdit($name)
    {
        $form = new Form($this, $name);

        $dataResource = new \FlatArray($this->resourcesDataTree);
        $flatDataResource = $dataResource->getArray(TRUE);
        $flatDataResource[0] = 'Všechny zdroje';

        $dataRoles = new \FlatArray($this->roleDataTree);
        $flatDataRoles = $dataRoles->getArray(TRUE);

        $dataPrivileges = new \FlatArray($this->privilegesDataTree);
        $flatDataPrivileges = $dataPrivileges->getArray(TRUE);
        $flatDataPrivileges[0] = 'Všechna oprávnění';

        $access = array(1 => 'Povolit', 0 => 'Zamítnout');

        if($this->isEditPermission)
        {
            if(count($flatDataRoles))
                $form->addRadioList('role_id', 'Roles', $flatDataRoles);

            if(count($flatDataResource))
                $form->addRadioList('resource_id', 'Zdroj', $flatDataResource);

            if(count($flatDataPrivileges))
                $form->addRadioList('privilege_id', 'Privileges', $flatDataPrivileges);
        }

        $form->addRadioList('access', 'Access', $access)->addRule(Form::FILLED, 'Vyplňte povolení');

        if(!$this->isEditPermission)
        {
            $form->addSubmit('add', 'Vytvořit');
            $form->addSubmit('add_back', 'Vytvořit a zpět');
        }
        else
        {
            $form->addSubmit('edit', 'Uložit');
            $form->addSubmit('edit_back', 'Uložit a zpět');
        }

        $form->onSuccess[] = array($this, 'addEditOnFormSubmitted');
    }

    public function addEditOnFormSubmitted(Form $form)
    {
        $error = FALSE;
        $id = $this->getParam('id');
        $values = $form->getValues();

        if(!$this->isEditPermission)
        {
            $httpData = $form->getHttpData();

            $values['resource_id'] = $httpData['resource_id'];
            $values['role_id'] = $httpData['role_id'];
            $values['privilege_id'] = $httpData['privilege_id'];

            $error = FALSE;
            $this->database->beginTransaction();

            try
            {
                foreach($values['privilege_id'] as $privi)
                {
                    foreach($values['resource_id'] as $resou)
                    {
                        foreach($values['role_id'] as $role)
                        {
                            if($resou == '0')
                                $resou = NULL;

                            if($privi == '0')
                                $privi = NULL;

                            $this->context->AclModel->insert(array(
                              'role_id' => $role,
                              'privilege_id' => $privi,
                              'resource_id' => $resou,
                              'access' => $values['access'],
                            ));
                        }
                    }
                }

                $this->context->database->commit();
                $this->flashMessage('Práva byla přidělena', 'success autoclose');

                if($this->config['acl']['caching'])
                {
                    $this->cache->remove($this->config['acl']['cache_key']);
                }

                if($form->submitted->name === 'add_back')
                    $this->redirect('Permission:');
                else
                    $this->redirect('this');
            }
            catch(Exception $e)
            {
                $error = TRUE;
                $form->addError('Práva se nepodařilo přidělit', 'error autoclose');
                throw $e;
            }

            if($error)
                $this->database->rollback();
        }
        else
        {
            try
            {
                $values = (array) $values;

                if(empty($values['resource_id']))
                    $values['resource_id'] = NULL;

                if(empty($values['privilege_id']))
                    $values['privilege_id'] = NULL;

                $this->context->AclModel->update($id, $values);

                $this->flashMessage('Přístup byl upraven', 'success autoclose');

                if($this->config['acl']['caching'])
                {
                    $this->cache->remove($this->config['acl']['cache_key']);
                }

                if($form->submitted->name === 'edit_back')
                    $this->redirect('Permission:');
                else
                    $this->redirect('this');
            }
            catch(Exception $e)
            {
                $form->addError('Přístup se nepodařilo upravit!', 'error autoclose');
                throw $e;
            }
        }
    }

    public function handleDeletePermission($rID)
    {
        try
        {
            $this->context->AclModel->delete($rID);
            $this->flashMessage('Přístup byl odebrán.', 'success autoclose');
            if($this->config['acl']['caching'])
            {
                $this->cache->remove($this->config['acl']['cache_key']);
            }
            $this->redirect('Permission:');
        }
        catch(Exception $e)
        {
            $this->flashMessage('Přístup se nepoařilo odebrat', 'error autoclose');
            throw $e;
        }
    }

}

