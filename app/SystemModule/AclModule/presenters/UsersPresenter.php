<?php

namespace App\SystemModule\AclModule\Presenters;

use Model\Acl\AccessModel;
use \Nette\Application\UI\Form;
use \Nette\Caching\Cache;

class UsersPresenter extends \App\SystemModule\BasePresenter
{
	/** @var string */
	private $search = '';

	/** @var \Nette\Caching\Cache */
	private $cache;

	/** @var boolean */
	private $isEditUser = FALSE;

	/** @var array */
	private $roleDataTree;

	public function startup()
	{
		parent::startup();
		$this->cache = new Cache($this->context->cacheStorage, $this->config['acl']['namespace']);
	}

	public function renderLog()
	{
		$this->user_hasPermissions_CMS('users', 'view');

		$logs = $this->userLogModel->table_log();

		$vp = new \VisualPaginator($this, 'vp');
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = 20;
		$paginator->itemCount = $logs->count();

		$logs = $logs->limit($paginator->itemsPerPage, $paginator->offset)->order('created DESC');

		$this->template->logs = $logs;
	}

	public function renderLogin()
	{
	}

	/**
	 * Render default template
	 */
	public function renderDefault()
	{
		//$this->user_hasPermissions_CMS('users', 'view');

		$vp = new \VisualPaginator($this, 'vp');
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = 20;

		$users = $this->userService->table_users()->order('name DESC');

		$paginator->itemCount = $users->count();

		if(!empty($this->search))
		{
			$users = $users->where('name LIKE ?', $this->search);
			$paginator->itemsPerPage = $paginator->itemCount;
		}

		$users = $users->limit($paginator->itemsPerPage, $paginator->offset);

		$this->template->cancelFilter = $this->search ? TRUE : FALSE;
		$this->template->users = $users;
		$this->template->canDelete = TRUE; //$this->user_hasPermissions_CMS('users', 'delete', FALSE);
		$this->template->canEdit = TRUE; //$this->user_hasPermissions_CMS('users', 'edit', FALSE);
	}

	/**
	 * Create search Form
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentSearch()
	{
		$form = new Form();

		$form->addText('name', 'Name:', 30)->setRequired('Zadejte jméno')->setAttribute('placeholder', 'Vyhledat uživatele');
		$form->addSubmit('search', 'Vyhledat');
		$form->onSuccess[] = array($this, 'searchOnFormSubmitted');

		return $form;
	}

	/**
	 * Submit searchForm
	 * @param \Nette\Application\UI\Form $form
	 */
	public function searchOnFormSubmitted(Form $form)
	{
		$values = $form->getValues();
		$this->search = strtr($values['name'], "*", "%");
	}

	/**
	 * Render user detail
	 * @param int $id
	 */
	public function actionDetail($id = NULL)
	{
		$this->isEditUser = is_null($id) ? FALSE : TRUE;

		$this->roleDataTree = $this->context->AclRolesModel->getTreeValues(TRUE);

		$roles = array();
		$form = $this->getComponent('userForm');

		if($this->isEditUser)
		{
			$userData = $this->userService->table_users()->where('id', $id)->fetch();
			$roles = $this->context->AclRolesModel->getAllByUserPairs($id);

			if($userData)
			{
				$form->setDefaults($userData);
			}
			else
				$form->addError('Vybraný uživatel neexistuje!');
		}

		$this->template->roles = $roles;
		$this->template->dataTreeRole = $this->roleDataTree;
		$this->template->isEditUser = $this->isEditUser;
	}

	/**
	 * Create user edit form
	 * @param string $name
	 */
	public function createComponentUserForm($name)
	{
		$form = new Form($this, $name);

//		$form->addText('name', 'Uživatelské jméno: ', 30)->setDisabled();

		$form->addText('name', 'Jméno: ', 30)
			->addRule(Form::FILLED, 'Zadejte jméno');

		$form->addText('surname', 'Přijmení: ', 30)
			->addRule(Form::FILLED, 'Zadejte přijmení');

		$form->addText('email', 'E-mail: ', 50)
			->setRequired('Zadejte e-mail')
			->addRule(\Nette\Application\UI\Form::EMAIL, 'E-mail není ve správném tvaru')
			->addRule(array($this, 'verify_emailFree'), 'Zadaný e-mail je již použit');

		$form->addText('phone', 'Telefon: ', 50);

		if($this->isEditUser)
			$form->addSubmit('edit', 'Upravit');
		else
			$form->addSubmit('add', 'Přidat');

		$form->onSuccess[] = array($this, 'userFormSubmitted');
	}

	/**
	 * Submit for user form
	 * @param \Nette\Application\UI\Form $form
	 * @throws \App\AclModule\Exception
	 */
	public function userFormSubmitted(Form $form)
	{
		$error = FALSE;
		$this->context->database->beginTransaction();

		$httpData = $form->getHttpData();

		$values = $form->getValues();
		$roles = $httpData['role_id'];

		$userData = array();
		$userData['name'] = $values->name . ' ' . $values->surname;
		$userData['email'] = $values->email;
		$userData['phone'] = $values->phone;

		try
		{
			if($this->isEditUser)
			{
				$id = $this->getParam('id');
				$this->userService->i_user($userData, $id);
				$this->context->AclUsersModel->deleteUserRole($id);
			}
			else
			{
				$id = $this->userService->i_user($userData, NULL);
			}

			if(count($roles))
			{
				foreach($roles as $role)
				{
					$this->getContext()->AclUsersModel->insertUserRole(array(
						'user_id' => $id,
						'role_id' => $role,
					));
				}
			}

			if($this->isEditUser)
				$this->flashMessage('Uživatel byl v pořádku upraven.', 'success');
			else
				$this->flashMessage('Uživatel byl vytvořen.', 'success');

			$this->context->database->commit();
			$this->redirect('this');

			if($this->context->parameters['acl']['caching'])
				$this->cache->remove($this->context->parameters['acl']['cache_key']); // delete cache
		}
		catch(Exception $e)
		{
			$error = TRUE;

			if($this->isEditUser)
				$this->flashMessage('Uživatele se nepodařilo upravit.', 'error');
			else
				$this->flashMessage('Uživatele se nepodařilo vytvořit.', 'error');

			throw $e;
		}

		if($error)
			$this->context->database->rollback();
	}

	/*     * ****************
	 * Delete
	 * **************** */
	public function actionDelete($id)
	{
		$data = $this->getContext()->AclUsersModel->getName($id);
		if($data)
		{
			$this->template->user_name = $data;
		}
		else
		{
			$this->flashMessage('This user does not exist.');
			$this->redirect('Users:');
		}
	}

	protected function createComponentDelete($name)
	{
		$form = new Form($this, $name);
		$form->addSubmit('delete', 'Delete');
		$form->addSubmit('cancel', 'Cancel');
		$form->onSuccess[] = array($this, 'deleteOnFormSubmitted');
	}

	public function deleteOnFormSubmitted(Form $form)
	{
		if($form['delete']->isSubmittedBy())
		{
			try
			{
				$id = $this->getParam('id');
				$this->getContext()->AclUsersModel->delete($id);
				$this->flashMessage('The user has been deleted.', 'ok');
				if($this->getContext()->parameters['acl']['caching'])
				{
					$this->cache->remove($this->getContext()->parameters['acl']['cache_key']); // delete cache
				}
				$this->redirect('Users:');
			}
			catch(Exception $e)
			{
				$form->addError('The user has not been deleted.');
				throw $e;
			}
		}
		else
			$this->redirect('Users:');
	}

	//////////// ACCESS ***********************************************z*v*** */

	public function actionAccess($id)
	{
		$this->template->nodes = $this->getContext()->AclRolesModel;
		$this->template->parents = $this->getContext()->AclRolesModel->getChildNodes(NULL);

		$this->template->users = $this->userService->table_users()->where('id', $id)->fetch();

		$roles = $this->getContext()->AclRolesModel->getAllByUser($id);

		$accessModel = new AccessModel($this->context->database, $roles);

		$this->template->access = $accessModel->getAccess();
	}

	public function renderLogout()
	{
		$this->user->logout();
		$this->redirect('Users:login');
	}

	/**
	 * Ověří, zda se už s daným emailem někdo neregistroval
	 * @param $item
	 * @return bool
	 */
	public function verify_emailFree($item)
	{
		return !$this->userService->verify_freeEmail(\Nette\Utils\Strings::lower($item->value));
	}
}
