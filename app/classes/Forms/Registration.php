<?php
namespace classes\Forms;
use Nette;
use Nette\Application\UI;
use Nette\Forms;

class Registration extends BaseForm {

    public function __construct($password = FALSE)
    {
        parent::__construct();

        $this->addText('name', 'Jméno / Firma')
            ->addRule(Forms\Form::FILLED, 'Please provide a username.');

        $this->addText('street', 'Ulice')
            ->addCondition(UI\Form::FILLED);

        $this->addText('city', 'Město')
            ->addCondition(UI\Form::FILLED);

        $this->addText('zip', 'PSČ')
            ->addCondition(UI\Form::FILLED)
            ->addRule(UI\Form::INTEGER, 'PSČ může obsahovat pouze čísla')
            ->addRule(UI\Form::LENGTH, 'PSČ může musí být %d znaků dlouhé', 5);

        $this->addText('ico', 'IČ')
            ->addCondition(UI\Form::FILLED)
            ->addRule(array($this, 'verifyIC'), 'IČ není ve správném tvaru');

        $this->addText('dic', 'DIČ')
            ->addCondition(UI\Form::FILLED)
            ->addRule(array($this, 'verifyDIC'), 'DIČ není ve správném tvaru');

        if($password)
        {
            $this->addPassword('password', 'Heslo: *', 20)
                ->setRequired('Zvolte si heslo')
                ->addRule(UI\Form::MIN_LENGTH, 'Heslo musí obsahovat minimálně %d znaků', 6)
                ->setAttribute('autocomplete', 'off');

            $this->addPassword('passwordRepeat', 'Heslo znovu: *', 20)
                ->setRequired('Zopakujte heslo pro kontrolu')
                ->addRule(UI\Form::EQUAL, 'Hesla se neshodují', $this['password'])
                ->setAttribute('autocomplete', 'off');
        }

        $this->addProtection('Please submit this form again (security token has expired).');
        $this->addSubmit('register', 'Zaregistrovat se');
    }

    public function verifyIC($item)
    {
        return \zvitek\Helper::verifyIC($item->value);
    }

    public function verifyDIC($item)
    {
        return \zvitek\Helper::verifyDIC($item->value);
    }
}