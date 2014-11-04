<?php

namespace App\SystemModule\AclModule\Presenters;

use \Nette\Application\UI\Form;
use Nette\Caching\Cache;

class PrivilegesPresenter extends \App\SystemModule\BasePresenter
{
    /** @var $cache Cache */
    private $cache;

    /** @var bool */
    private $isEditPrivileges = FALSE;

    /** @var int */
    private $id;

    public function startup()
    {
        parent::startup();
        $this->cache = new Cache($this->context->cacheStorage, $this->config['acl']['namespace']);
    }

    public function renderDefault()
    {
        $this->template->privileges = $this->context->AclPrivilegesModel->getAll();
    }

    public function actionDetail($id = 0)
    {
        $this->isEditPrivileges = $id == 0 ? FALSE : TRUE;

        if($this->isEditPrivileges)
            $this->id = $id;

        $data = $this->context->AclPrivilegesModel->get($this->id);

        if($data)
        {
            $form = $this->getComponent('addEdit');
            $form->setDefaults($data);
        }
    }


    protected function createComponentAddEdit($name)
    {
        $form = new Form($this, $name);

        $form->addText('name', 'Name', 30)->addRule(Form::FILLED, 'Zadejte název oprávnění');
        $form->addText('key_name', 'Klíč', 30);

        $form->addTextArea('comment', 'Comment', 40, 4)
        ->addRule(Form::MAX_LENGTH, 'Komentář musí být minimálně %d znaků dlouhý', 50);

        if($this->isEditPrivileges)
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
        $values = $form->getValues();

        try
        {
            if(!$this->isEditPrivileges)
            {
                $this->id = $this->context->AclPrivilegesModel->insert($values);
                $this->flashMessage('Oprávnění přidáno', 'success autoclose');
            }
            else
            {
                $this->context->AclPrivilegesModel->update($this->id, $values);
                $this->flashMessage('Oprávnění upraveno', 'success autoclose');
            }

            if($this->config['acl']['caching'])
            {
                $this->cache->remove($this->config['acl']['cache_key']);
            }

            if($form->submitted->name == 'edit_back' || $form->submitted->name == 'save_back')
                $this->redirect('Privileges:');
            else
                $this->redirect('Privileges:detail', array('id' => $this->id));
        }
        catch(Exception $e)
        {
            if($this->isEditPrivileges)
                $this->flashMessage('Oprávnění se nepodařilo upravit', 'error autoclose');
            else
                $this->flashMessage('Oprávnění se nepodařilo přidat', 'error autoclose');

            $this->redirect('this');
        }
    }

    public function handleRemovePrivileges($rID)
    {
        try
        {
            $this->context->AclPrivilegesModel->delete($rID);
            $this->flashMessage('Oprávnění bylo odstraněno', 'success autoclose');

            if($this->config['acl']['caching'])
            {
                $this->cache->remove($this->config['acl']['cache_key']);
            }
            $this->redirect('Privileges:');
        }
        catch(Exception $e)
        {
            $this->flashMessage('Oprávnění se npodařilo odstranit', 'error autoclose');
            $this->redirect('this');
            throw $e;
        }
    }

}
