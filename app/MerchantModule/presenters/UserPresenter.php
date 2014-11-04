<?php

namespace App\MerchantModule\Presenters;

use Nette;
use App\Model;

class UserPresenter extends BasePresenter
{
	public function renderDefault()
	{

	}

    /**
     * Create Component Registration Merchant
     * @return \App\Merchant\control\RegistrationControl
     */
    public function createComponentRegistration()
    {
        return new \App\Merchant\control\RegistrationControl($this->merchantService, $this->config);
    }
}