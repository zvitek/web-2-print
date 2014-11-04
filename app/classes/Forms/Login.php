<?php
namespace classes\Forms;
use Nette;
use Nette\Application\UI;
use Nette\Forms;

class Login extends BaseForm {

    public function __construct()
    {
        parent::__construct();

        $this->addText('username', 'Username:')
            ->addRule(Forms\Form::FILLED, 'Please provide a username.');

        $this->addPassword('password', 'Password:')
            ->addRule(Forms\Form::FILLED, 'Please provide a password.');

        $this->addProtection('Please submit this form again (security token has expired).');
        $this->addSubmit('login', 'Login');
    }
}