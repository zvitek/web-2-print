<?php

namespace App\SystemModule\AclModule\Presenters;
use \Nette\Application\UI\Form;
use \Nette\Caching\Cache;

class ResourcesPresenter extends \App\SystemModule\BasePresenter
{
    /* @var Cache */
    private $cache;

    /* @var array */
    protected $resourcesDataTree;

    /* @var bool */
    protected $isEditResource;

    public function startup()
    {
        parent::startup();
        $this->cache = new Cache($this->context->cacheStorage, $this->config['acl']['namespace']);
    }

    public function renderDefault()
    {
        $this->template->nodes = $this->context->AclResourcesModel;
        $this->template->parents = $this->context->AclResourcesModel->getChildNodes(NULL);

        $this->template->canDelete = TRUE;
        $this->template->canEdit = TRUE;
        $this->template->canCreate = TRUE;
    }

    public function actionDetail($id = NULL)
    {
        $this->isEditResource = is_null($id) ? FALSE : TRUE;
        $this->resourcesDataTree = $this->context->AclResourcesModel->getTreeValues(TRUE);

        $form = $this->getComponent('addEdit');


        if($this->isEditResource)
            $this->user_hasPermissions('resources', 'edit');
        else
            $this->user_hasPermissions('resources', 'create');

        if($this->isEditResource)
        {
            $data = $this->context->AclResourcesModel->get($id);

            if($data)
            {
                if(empty($data->parent_id))
                    $data->parent_id = 0;

                $form->setDefaults($data);
            }
            else
                $form->addError('Zadaný zdroj neexistuje!');
        }

        $this->template->dataTree = $this->resourcesDataTree;
        $this->template->isEditResource = $this->isEditResource;
    }

    protected function createComponentAddEdit($name)
    {
        $flatData = new \FlatArray($this->resourcesDataTree);
        $flatDataResource = $flatData->getArray(TRUE);

        $flatDataResource[0] = 'Hlavní zdroj';

        $form = new Form($this, $name);

        if($this->config['acl']['prog_mode'])
            $form->addText('name', 'Název zdroje', 30)->setRequired('Musíte vyplnit název zdroje')->getControlPrototype()->onChange("create_key()");
        else
            $form->addText('name', 'Name', 30)->setRequired('Musíte vyplnit název zdroje');

        $form->addText('key_name', 'Key', 30)->setDisabled(($this->config['acl']['prog_mode'] ? false : true));

        if(count($flatDataResource))
            $form->addRadioList('parent_id', 'Parent', $flatDataResource);

        $form->addTextArea('comment', 'Comment', 40, 4)->addRule(Form::MAX_LENGTH, 'Comment must be at least %d characters.', 250);

        if(!$this->isEditResource)
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
        if(!$this->isEditResource)
        {
            try
            {
                $values = $form->getValues();

                if($values['parent_id'] == 0)
                    $values['parent_id'] = NULL;

                $resourceID = $this->context->AclResourcesModel->insert($values);

                $this->flashMessage('Zdroj byl vytvořen', 'success');

                if($this->config['acl']['caching'])
                {
                    $this->cache->remove($this->config['acl']['cache_key']);
                }

                if($form->submitted->name === 'add_back')
                    $this->redirect('Resources:');
                else
                    $this->redirect('Resources:detail', array('id' => $resourceID));
            }
            catch(Exception $e)
            {
                $form->addError('Zdroj se nepodařilo vytvořit', 'error');
                throw $e;
            }
        }
        else
        {
            try
            {
                $id = $this->getParam('id');
                $values = $form->getValues();

                if($values['parent_id'] == 0)
                    $values['parent_id'] = NULL;

                $this->context->AclResourcesModel->update($id, $values);

                $this->flashMessage('Zdroj byl upraven', 'success');

                if($this->config['acl']['caching'])
                {
                    $this->cache->remove($this->config['acl']['cache_key']);
                }

                if($form->submitted->name === 'edit_back')
                    $this->redirect('Resources:');
                else
                    $this->redirect('this');
            }
            catch(Exception $e)
            {
                $form->addError('Zdroj se nepodařilo upravit', 'error');
                throw $e;
            }
        }
    }

    public function handleDelete($id)
    {
        $this->user_hasPermissions('resources', 'delete');

        $this->context->AclResourcesModel->delete($id);

        $this->flashMessage('Zdroj byl odstraněn!', 'success');

        if($this->config['acl']['caching'])
        {
            $this->cache->remove($this->config['acl']['cache_key']);
        }

        $this->redirect('this');
    }

}

