<?php
namespace App\Merchant\control;

use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

class RegistrationControl extends \Nette\Application\UI\Control {

    /** @var \App\Model\Merchant\MerchantService */
    private $merchantService;

    /** @var array */
    private $config = array();

    public function __construct(\App\Model\Merchant\MerchantService $merchantService, $config)
    {
        $this->merchantService = $merchantService;
        $this->config = $config;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/Registration.latte');
        $this->template->render();
    }

    protected function createComponentRegistrationForm()
    {
        $form = new \classes\Forms\Registration();
        $form->addTo(new \classes\Forms\Password());

        $form->addText('system_url', 'Adresa')
            ->addCondition(Form::FILLED)
            ->addRule(Form::URL, 'Adresa není ve správném tvaru');

        $form->addText('email', 'E-mail')
            ->addRule(Form::EMAIL, 'E-mail není ve správném tvaru')
            ->addRule(array($this, 'verify_emailFree'), 'Zadaný e-mail je již použit');

        $form->onSuccess[] = array($this, 'registrationForm_submitted');
        return $form;
    }

    public function registrationForm_submitted(\Nette\Application\UI\Form $form)
    {
        $data = array();
        $values = $form->getValues();

        dump($values);

        $data['name'] = $values->name;
        $data['email'] = Strings::lower($values->email);
        $data['street'] = $values->street;
        $data['city'] = $values->city;
        $data['zip'] = $values->zip;
        $data['ico'] = \zvitek\Helper::pick($values->ico);
        $data['dic'] = \zvitek\Helper::pick($values->dic);

        $data['system_name'] = $this->merchantService->verify_systemName($data['name']);
        $data['system_url'] = \zvitek\Helper::pick($values->system_url);

        $data['created'] = new DateTime();

        return;
        $merchant = $this->merchantService->i_control($data);

        if($merchant)
        {
            $this->merchantService->i_merchantRoles($merchant, $this->config['acl']['defaultRole']['merchant']);
        }
        else
        {
            $form->addError('Uživatele se nepodařilo vytvořit.');
        }
    }

    public function verify_emailFree($item)
    {
        return !$this->merchantService->verify_freeEmail(\Nette\Utils\Strings::lower($item->value));
    }
}