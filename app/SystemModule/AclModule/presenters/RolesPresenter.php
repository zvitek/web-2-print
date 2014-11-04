<?php

namespace App\SystemModule\AclModule\Presenters;

use Model\Acl\AccessModel;
use \Nette\Application\UI\Form;
use Nette\Caching\Cache;

class RolesPresenter extends \App\SystemModule\BasePresenter
{
    /** @var $cache Cache */
    private $cache;

    /* @var array */
    protected $roleDataTree;

    /** @var bool */
    private $isEditRole = FALSE;

    /** @var int */
    private $id;

    public function startup()
    {
        parent::startup();
        $this->cache = new Cache($this->context->cacheStorage, $this->config['acl']['namespace']);
    }

    public function renderDefault()
    {
        $this->template->nodes = $this->context->AclRolesModel;
        $this->template->parents = $this->context->AclRolesModel->getChildNodes(NULL);
    }

    public function actionDetail($id = 0)
    {
        $parent = 0;
        $this->isEditRole = $id == 0 ? FALSE : TRUE;
        $this->id = $id;

        $this->roleDataTree = $this->context->AclRolesModel->getTreeValues(TRUE);

        $data = $this->context->AclRolesModel->get($this->id);

        if($data)
        {
            $form = $this->getComponent('addEdit');

            if($data->parent_id !== NULL)
                $parent = $data->parent_id;

            $form->setDefaults($data);
        }

        $this->template->dataTreeRole = $this->roleDataTree;
        $this->template->isEditRole = $this->isEditRole;
        $this->template->selectedParent = $parent;
    }

    protected function createComponentAddEdit($name)
    {
        $dataRoles = new \FlatArray($this->roleDataTree);
        $flatDataRoles = $dataRoles->getArray(TRUE);
        $flatDataRoles[0] = 'Hlavní';

        $form = new Form($this, $name);

        $form->addText('name', 'Name', 30)
        ->addRule(Form::FILLED, 'Zadejte název role');

        $form->addText('key_name', 'Klíč', 30)
        ->addRule(Form::FILLED, 'Zadejte klíč role');

        if($this->isEditRole)
        {
            if(count($flatDataRoles))
                $form->addRadioList('role_id', 'Roles', $flatDataRoles);
        }

        $form->addTextArea('comment', 'Komentář', 40, 4)
        ->addRule(Form::MAX_LENGTH, 'Komentář musí být minimálně %d znaků dlouhý.', 50);

        if($this->isEditRole)
        {
            $form->addSubmit('edit', 'Uožit');
            $form->addSubmit('edit_back', 'Uložit a zpět');
        }
        else
        {
            $form->addSubmit('save', 'Vytvořit');
            $form->addSubmit('save_back', 'Vytvořit a zpět');
        }

        $form->onSuccess[] = array($this, 'addEditOnFormSubmitted');
    }

    public function addEditOnFormSubmitted(Form $form)
    {
        $httpData = $form->getHttpData();

        $values = $form->getValues();
        $values->parent_id = NULL;

        if(array_key_exists('role_id', $httpData))
        {
            $values->parent_id = $httpData['role_id'] ? $httpData['role_id'] : NULL;
        }

        unset($values->role_id);

        try
        {
            if(!$this->isEditRole)
            {
                $this->id = $this->context->AclRolesModel->insert($values);
                $this->flashMessage('Role byla přidána', 'success autoclose');
            }
            else
            {
                $this->context->AclRolesModel->update($this->id, $values);
                $this->flashMessage('Role byla upravena', 'success autoclose');
            }

            if($this->config['acl']['caching'])
            {
                $this->cache->remove($this->config['acl']['cache_key']);
            }

            if($form->submitted->name == 'edit_back' || $form->submitted->name == 'save_back')
                $this->redirect('Roles:');
            else
                $this->redirect('Roles:detail', array('id' => $this->id));
        }
        catch(Exception $e)
        {
            if($this->isEditRole)
                $this->flashMessage('Roli se nepodařilo upravit', 'error autoclose');
            else
                $this->flashMessage('Roli se nepodařilo přidat', 'error autoclose');

            $this->redirect('this');
        }
    }

    public function handleRemoveRole($rID)
    {
        try
        {
            $this->context->AclRolesModel->delete($rID);
            $this->flashMessage('Role byla odstraněna.', 'success autoclose');

            if($this->config['acl']['caching'])
            {
                $this->cache->remove($this->config['acl']['cache_key']);
            }
            $this->redirect('Roles:');
        }
        catch(Exception $e)
        {
            $this->flashMessage('Roli se nepodařilo odstranit.', 'error autoclose');
            $this->redirect('this');
            throw $e;
        }
    }

    public function actionUsers($id)
    {
        $this->template->nodes = $this->context->AclRolesModel;
        $this->template->parents = $this->context->AclRolesModel->getChildNodes(NULL);
        $this->template->role = $this->context->AclRolesModel->getName($id);
        $this->template->users = $this->context->AclUsersModel->getAllByRole($id);
    }

    public function actionAccess($id)
    {
        $this->template->nodes = $this->context->AclRolesModel;
        $this->template->parents = $this->context->AclRolesModel->getChildNodes(NULL);

        $role = $this->context->AclRolesModel->get($id);
        $this->template->role = $role->name;

        $accessModel = new AccessModel($this->database, array($role));
        $this->template->access = $accessModel->getAccess();
    }

}
