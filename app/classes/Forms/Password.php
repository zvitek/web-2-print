<?php
namespace classes\Forms;
use Nette;
use Nette\Application\UI;
use Nette\Forms;

class Password extends BaseForm {

    public function __construct()
    {
        parent::__construct();

        $this->addPassword('password', 'Heslo: *', 20)
            ->setRequired('Zvolte si heslo')
            ->addRule(UI\Form::MIN_LENGTH, 'Heslo musí obsahovat minimálně %d znaků', 6)
            ->setAttribute('autocomplete', 'off');

        $this->addPassword('passwordRepeat', 'Heslo znovu: *', 20)
            ->setRequired('Zopakujte heslo pro kontrolu')
            ->addRule(UI\Form::EQUAL, 'Hesla se neshodují', $this['password'])
            ->setAttribute('autocomplete', 'off');
    }
}