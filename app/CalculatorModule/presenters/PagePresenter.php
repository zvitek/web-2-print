<?php

namespace App\CalculatorModule\Presenters;

use classes\Forms\Login;
use classes\Forms\Services_form;
use Nette;
use App\Model;

class PagePresenter extends BasePresenter
{
	public function renderDefault()
	{

	}

    protected function createComponentLoginForm()
    {
        $form = new Nette\Application\UI\Form();
        $form->addCheckboxList('sdfd', 'ßfsd', array());
        $form->addText('test', 'sdfsdf');
        $form->addSubmit('teste', 'test');
        $form->onSuccess[] = \callback($this, 'loginFormSubmitted');
        return $form;
    }

    public function loginFormSubmitted(Nette\Application\UI\Form $form)
    {
        $values = $form->getValues();
        dump($form->getHttpData());
        dump($values);
        /*
        try {
            $user = $this->getUser();
            $user->login($form['username']->getValue(), $form['password']->getValue());
            $this->log(Log::TYPE_INFO, Log::ACTION_LOGIN);
            $this->restoreRequest($this->backlink);
            $this->redirect('Admin:Dashboard');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
        */
    }
}